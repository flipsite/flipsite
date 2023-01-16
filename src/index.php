<?php

declare(strict_types=1);

use Flipsite\App\CustomErrorHandler;
use Flipsite\App\Middleware\CssMiddleware;
use Flipsite\App\Middleware\DiagnosticsMiddleware;
use Flipsite\App\Middleware\ExpiresMiddleware;
use Flipsite\App\Middleware\OfflineMiddleware;
use Flipsite\App\Middleware\SvgMiddleware;
use Flipsite\Assets\ImageHandler;
use Flipsite\Assets\VideoHandler;
use Flipsite\Builders\IntegrationsBuilder;
use Flipsite\Builders\ComponentBuilder;
use Flipsite\Builders\DocumentBuilder;
use Flipsite\Builders\FaviconBuilder;
use Flipsite\Builders\FontBuilder;
use Flipsite\Builders\MetaBuilder;
use Flipsite\Builders\ScriptBuilder;
use Flipsite\Builders\PreloadBuilder;
use Flipsite\Components\ComponentFactory;
use Flipsite\Utils\Path;
use Flipsite\Utils\Robots;
use Flipsite\Utils\Sitemap;
use League\Container\Container;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Flipsite\Utils\StyleAppearanceHelper;

require_once getenv('VENDOR_DIR') . '/autoload.php';

if (!getenv('APP_BASEPATH')) {
    $basePath = str_replace('/index.php', '', $_SERVER['SCRIPT_NAME']);
    $protocol = 'on' === ($_SERVER['HTTPS'] ?? '') ? 'https' : 'http';
    putenv('APP_BASEPATH=' . $basePath);
    putenv('APP_SERVER=' . $protocol . '://' . $_SERVER['HTTP_HOST']);
} else {
    $protocol = 'on' === ($_SERVER['HTTPS'] ?? '') ? 'https' : 'http';
    putenv('APP_SERVER=' . $protocol . '://' . $_SERVER['HTTP_HOST']);
}

$container = new Container();
$container->add('environment', 'Flipsite\Environment', true);
$container->add('caniuse', 'Flipsite\Utils\CanIUse', true);
$container->add('plugins', 'Flipsite\Utils\Plugins', true)->addArgument($plugins ?? []);
$container->add('reader', 'Flipsite\Data\Reader', true)->addArgument('environment')->addArgument('plugins');

AppFactory::setContainer($container);
$app = AppFactory::create();
$app->addBodyParsingMiddleware();
$app->setBasePath(getenv('APP_BASEPATH'));

$cssMw         = new CssMiddleware($container->get('reader')->get('theme'), $container->get('caniuse'));
$diagnosticsMw = new DiagnosticsMiddleware();
$expiresMw     = new ExpiresMiddleware(365 * 86440);
$offlineMw     = new OfflineMiddleware($container->get('environment'), $container->get('reader'));
$svgMw         = new SvgMiddleware($container->get('environment'));

$app->get('/img[/{file:.*}]', function (Request $request, Response $response, array $args) {
    $environment = $this->get('environment');
    $handler     = new ImageHandler(
        $environment->getAssetSources(),
        $environment->getImgDir(),
        $environment->getImgBasePath(),
    );
    return $handler->getResponse($response, $args['file']);
})->add($expiresMw);

$app->get('/videos[/{file:.*}]', function (Request $request, Response $response, array $args) {
    $environment = $this->get('environment');
    $handler     = new VideoHandler(
        $environment->getSiteDir().'/assets',
        $environment->getVideoDir(),
        $environment->getVideoBasePath(),
    );
    return $handler->getResponse($response, $args['file']);
})->add($expiresMw);

$app->get('/sitemap.xml', function (Request $request, Response $response) {
    $environment = $this->get('environment');
    $reader      = $this->get('reader');
    $sitemap     = new Sitemap($environment->getServer(), $reader->getSlugs());
    $response->getBody()->write((string) $sitemap);
    return $response->withHeader('Content-type', 'application/xml');
});

$app->get('/sw.{version}.js', function (Request $request, Response $response, $args) {
    $environment = $this->get('environment');
    $js          = file_get_contents(__DIR__.'/../js/sw.js');
    if (preg_match('/[abcdef0-9]{6}/', $args['version'])) {
        $js = str_replace('const OFFLINE_VERSION=1', 'const OFFLINE_VERSION="'.$args['version'].'"', $js);
    }
    $response->getBody()->write((string) $js);
    return $response->withHeader('Content-type', 'text/javascript');
})->add($expiresMw);

$app->get('/robots.txt', function (Request $request, Response $response) {
    $environment = $this->get('environment');
    $robots      = new Robots($environment->isLive(), $environment->getServer());
    $response->getBody()->write((string) $robots);
    return $response->withHeader('Content-type', 'text/plain');
});

$app->get('/manifest.json', function (Request $request, Response $response) {
    $environment   = $this->get('environment');
    $imageHandler  = new ImageHandler(
        $environment->getAssetSources(),
        $environment->getImgDir(),
        $environment->getImgBasePath(),
    );

    $toHex   = function (string $color): string {
        $rgb = SSNepenthe\ColorUtils\Colors\ColorFactory::fromString($color)->getRgb()->toArray();
        return sprintf('#%02x%02x%02x', $rgb['red'], $rgb['green'], $rgb['blue']); // #0d00ff
    };
    $reader                 = $this->get('reader');
    $manifest               = [];
    $manifest['short_name'] = $reader->get('name');
    $manifest['name']       = $reader->get('name');
    $manifest['icons']      = [];

    $icons = $reader->get('favicon');
    $image = $imageHandler->getContext($icons[512], ['width' => 512, 'height' => 512]);
    $icon  = [
        'src'  => $image->getSrc(),
        'type' => 'image/png',
        'sizes'=> '512x512'
    ];
    $manifest['icons'][]          = $icon;
    $manifest['start_url']        = '/';
    $manifest['background_color'] = $toHex($reader->get('pwa.bgColor') ?? $reader->get('theme.colors.dark') ?? '#ffffff');
    $manifest['display']          = 'minimal-ui';
    $manifest['scope']            = '/';
    $manifest['theme_color']      = $toHex($reader->get('pwa.themeColor') ?? $reader->get('theme.colors.primary'));

    // $environment = $this->get('environment');
    // $reader = $this->get('reader');
    // $sitemap = new Sitemap($environment->getServer(), $reader->getSlugs());
    $response->getBody()->write(json_encode($manifest));
    return $response->withHeader('Content-type', 'application/json');
});

$app->get('/files/[{file:.*}]', function (Request $request, Response $response, array $args) {
    $environment = $this->get('environment');
    $filepath    = $environment->getSiteDir().'/files/'.$args['file'];
    if (!file_exists($filepath)) {
        $response = $response->withStatus(302);
        $redirect = trim($environment->getServer());
        return $response->withHeader('Location', urlencode($redirect));
    }

    $pathinfo  = pathinfo($filepath);
    $extension = $pathinfo['extension'];
    $fileName  = $pathinfo['basename'];

    $response = $response->withHeader('Content-Type', 'application/'.$extension);
    $response = $response->withHeader('Content-Disposition', sprintf('attachment; filename="%s"', $fileName));

    $body = $response->getBody();
    $body->rewind();
    $body->write(file_get_contents($filepath));

    return $response;
});

$app->get('[/{path:.*}]', function (Request $request, Response $response, array $args) {
    $reader  = $this->get('reader');
    $plugins = $this->get('plugins');

    // Parse request path to determine language and requested page
    $path = new Path(
        $args['path'] ?? '',
        $reader->getDefaultLanguage(),
        $reader->getLanguages(),
        $reader->getSlugs(),
        $reader->getRedirects()
    );

    // Check if the requested path needs to redirect
    $redirect    = $path->getRedirect();
    $environment = $this->get('environment');
    if (null !== $redirect) {
        // Check if internal url
        $parsedUrl = parse_url($redirect);
        if (!isset($parsedUrl['scheme'])) {
            $redirect = trim($environment->getServer() . '/' . urlencode($redirect), '/');
        }
        return $response->withStatus(302)->withHeader('Location', $redirect);
    }

    $documentBuilder  = new DocumentBuilder($environment, $reader, $path);
    $componentBuilder = new ComponentBuilder($request, $environment, $reader, $path, $this->get('caniuse'));
    $componentBuilder->addFactory(new ComponentFactory());
    foreach ($reader->getComponentFactories() as $class) {
        $componentBuilder->addFactory(new $class());
    }

    $metaBuilder = new MetaBuilder($environment, $reader, $path);

    $faviconBuilder = new FaviconBuilder($environment, $reader);

    $scriptBuilder = new ScriptBuilder(
        $reader->getHash(),
        $environment->getBasePath(),
        (bool)$reader->get('offline')
    );
    $componentBuilder->addListener($scriptBuilder);

    $perloadBuilder = new PreloadBuilder();
    $componentBuilder->addListener($perloadBuilder);

    $page = $path->getPage();

    foreach ($reader->getSections($page, $path->getLanguage()) as $sectionId => $sectionData) {
        $sectionData = $plugins->run('section', $sectionData);
        $section     = $componentBuilder->build('group', $sectionData, [], $reader->get('theme.appearance') ?? 'light');
        $documentBuilder->addSection($section);
    }

    // Add body class TODO fix
    $bodyStyle = StyleAppearanceHelper::apply(
        \Flipsite\Utils\ArrayHelper::merge($componentBuilder->getStyle('body'), $reader->get('theme.components.body') ?? []),
        $reader->get('theme.appearance') ?? 'light'
    );
    $documentBuilder->addBodyStyle($bodyStyle);

    $document = $documentBuilder->getDocument();

    if ('offline' !== $page) {
        // Add Meta
        $document = $metaBuilder->getDocument($document);

        // Add Favicon
        $document = $faviconBuilder->getDocument($document);

        // Add Preload builder
        $document = $perloadBuilder->getDocument($document);

        // Add Webfonts
        $fonts = $reader->get('theme.fonts');
        if (null !== $fonts) {
            $fontBuilder = new FontBuilder($fonts);
            $document    = $fontBuilder->getDocument($document);
        }

        if ($reader->get('offline')) {
            $manifestLink = new \Flipsite\Components\Element('link', true, true);
            $manifestLink->setAttribute('rel', 'manifest');
            $baseUrl = $environment->getBasePath();
            $manifestLink->setAttribute('href', $baseUrl.'/manifest.json');
            $document->getChild('head')->addChild($manifestLink);
        }
    } else {
        $document->getChild('head')->getChild('title')->setContent('OFFLINE');
    }

    $bodyHtml = $document->getChild('body')->render(2, 1);
    if (strpos($bodyHtml, 'scroll:')) {
        $componentBuilder->dispatch(new Flipsite\Components\Event('ready-script', 'scroll', file_get_contents(__DIR__.'/../js/ready.scroll.min.js')));
    }
    if (strpos($bodyHtml, 'stuck:')) {
        $componentBuilder->dispatch(new Flipsite\Components\Event('ready-script', 'stuck', file_get_contents(__DIR__.'/../js/ready.stuck.min.js')));
    }
    if (strpos($bodyHtml, 'enter:')) {
        $componentBuilder->dispatch(new Flipsite\Components\Event('ready-script', 'enter', file_get_contents(__DIR__.'/../js/ready.enter.min.js')));
    }
    // Add Scripts
    $document = $scriptBuilder->getDocument($document);

    // Add Analytics
    $integrations = $reader->get('integrations');
    if (null !== $integrations) {
        $analyticsBuilder = new IntegrationsBuilder($environment->isLive(), $integrations);
        $document         = $analyticsBuilder->getDocument($document);
    }
    $document->getChild('head')->addChild(new Flipsite\Components\CustomCode('<style></style>'));

    // Custom HTML
    $customCodeFile = $environment->getSiteDir().'/custom.html';
    if (file_exists($customFile)) {
        $customCodeBuilder = new CustomCodeBuilder($environment->isLive(), $page, $customCodeFile);
        $document          = $customCodeBuilder->getDocument($document);
    }

    // If any plugins
    $document = $plugins->run('document', $document);

    $response->getBody()->write($document->render());

    return $response->withHeader('Content-type', 'text/html');
})->add($cssMw)->add($svgMw)->add($offlineMw)->add($diagnosticsMw);

$errorMiddleware = $app->addErrorMiddleware(true, true, true);
$errorMiddleware->setDefaultErrorHandler(new CustomErrorHandler($app, $cssMw));

$app->run();
