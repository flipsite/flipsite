<?php

declare(strict_types=1);

use Flipsite\App\CustomErrorHandler;
use Flipsite\App\Middleware\CssMiddleware;
use Flipsite\App\Middleware\DiagnosticsMiddleware;
use Flipsite\App\Middleware\ExpiresMiddleware;
use Flipsite\App\Middleware\OfflineMiddleware;
use Flipsite\App\Middleware\SvgMiddleware;
use Flipsite\App\Middleware\FlipsiteMiddleware;
use Flipsite\Assets\ImageHandler;
use Flipsite\Builders\AnalyticsBuilder;
use Flipsite\Builders\ComponentBuilder;
use Flipsite\Builders\DocumentBuilder;
use Flipsite\Builders\FaviconBuilder;
use Flipsite\Builders\FontBuilder;
use Flipsite\Builders\MetaBuilder;
use Flipsite\Builders\ScriptBuilder;
use Flipsite\Builders\PreloadBuilder;
use Flipsite\Builders\SectionBuilder;
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
$container->add('enviroment', 'Flipsite\Enviroment', true);
$container->add('caniuse', 'Flipsite\Utils\CanIUse', true);
$container->add('reader', 'Flipsite\Data\Reader', true)->addArgument('enviroment');
AppFactory::setContainer($container);
$app = AppFactory::create();
$app->addBodyParsingMiddleware();
$app->setBasePath(getenv('APP_BASEPATH'));

$cssMw         = new CssMiddleware($container->get('reader')->get('theme'), $container->get('caniuse'));
$diagnosticsMw = new DiagnosticsMiddleware();
$expiresMw     = new ExpiresMiddleware(365 * 86440);
$offlineMw     = new OfflineMiddleware($container->get('enviroment'), $container->get('reader'));
$svgMw         = new SvgMiddleware($container->get('enviroment'));

$app->get('/img[/{file:.*}]', function (Request $request, Response $response, array $args) {
    $enviroment = $this->get('enviroment');
    $handler = new ImageHandler(
        $enviroment->getImageSources(),
        $enviroment->getImgDir(),
        $enviroment->getImgBasePath(),
    );
    return $handler->getResponse($response, $args['file']);
})->add($expiresMw);

$app->get('/sitemap.xml', function (Request $request, Response $response) {
    $enviroment = $this->get('enviroment');
    $reader = $this->get('reader');
    $sitemap = new Sitemap($enviroment->getServer(), $reader->getSlugs());
    $response->getBody()->write((string) $sitemap);
    return $response->withHeader('Content-type', 'application/xml');
});

$app->get('/sw.js', function (Request $request, Response $response) {
    $enviroment = $this->get('enviroment');

    $js = file_get_contents(__DIR__.'/../js/sw.js');
    $response->getBody()->write((string) $js);
    return $response->withHeader('Content-type', 'text/javascript');
});

$app->get('/robots.txt', function (Request $request, Response $response) {
    $enviroment = $this->get('enviroment');
    $robots = new Robots($enviroment->isLive(), $enviroment->getServer());
    $response->getBody()->write((string) $robots);
    return $response->withHeader('Content-type', 'text/plain');
});

$app->get('/manifest.json', function (Request $request, Response $response) {
    $enviroment = $this->get('enviroment');
    $imageHandler = new ImageHandler(
        $enviroment->getImageSources(),
        $enviroment->getImgDir(),
        $enviroment->getImgBasePath(),
    );

    $toHex = function (string $color) : string {
        $rgb = SSNepenthe\ColorUtils\Colors\ColorFactory::fromString($color)->getRgb()->toArray();
        return sprintf('#%02x%02x%02x', $rgb['red'], $rgb['green'], $rgb['blue']); // #0d00ff
    };
    $reader = $this->get('reader');
    $manifest = [];
    $manifest['short_name'] = $reader->get('name');
    $manifest['name'] = $reader->get('name');
    $manifest['icons'] = [];

    $icons = $reader->get('favicon');
    $image = $imageHandler->getContext($icons[512], ['width' => 512, 'height' => 512]);
    $icon = [
        'src'  => $image->getSrc(),
        'type' => 'image/png',
        'sizes'=> '512x512'
    ];
    $manifest['icons'][] = $icon;
    $manifest['start_url'] = '/';
    $manifest['background_color'] = $toHex($reader->get('theme.colors.dark'));
    $manifest['display'] = 'minimal-ui';
    $manifest['scope'] = '/';
    $manifest['theme_color'] = $toHex($reader->get('theme.colors.primary'));

    // $enviroment = $this->get('enviroment');
    // $reader = $this->get('reader');
    // $sitemap = new Sitemap($enviroment->getServer(), $reader->getSlugs());
    $response->getBody()->write(json_encode($manifest));
    return $response->withHeader('Content-type', 'application/json');
});

$app->post('/form/submit/{formId}', function (Request $request, Response $response, array $args) {
    $enviroment = $this->get('enviroment');
    $form = $this->get('reader')->get('forms.'.$args['formId']);
    $parsedBody = $request->getParsedBody();
    $res = 'error';
    if (Flipsite\Utils\FormValidator::validate($form['data'], $form['required'] ?? [], $form['dummy'] ?? [], $parsedBody)) {
        if ('postmarkapp' === $form['type']) {
            try {
                $html = '';
                $body = '';
                foreach ($parsedBody as $attr => $val) {
                    if ($val) {
                        $html .= '<b>'.strtoupper($attr).':</b><br>';
                        $html .= $val.'<br><br>';
                        $body .= strtoupper($attr).":\r\n";
                        $body .= $val."\r\n\r\n";
                    }
                }
                $body .= '- flipsite';
                if ('localhost' === getenv('APP_ENV')) {
                    error_log('SUBJECT: '.$form['subject']);
                    error_log('BODY:');
                    error_log($body);
                } else {
                    $client     = new Postmark\PostmarkClient($form['token']);
                    $sendResult = $client->sendEmail(
                        'noreply@flipsite.io',
                        $form['to'],
                        $form['subject'],
                        $html,
                        $body
                    );
                }
                $res = 'success';
            } catch (Exception $generalException) {
                error_log(print_r($generalException->getMessage(), true));
                die();
            }
        }
    }
    $response = $response->withStatus(302);
    $redirect = trim($enviroment->getServer().'/'.$form['done'].'?res='.$res.'#'.$args['formId'], '/');
    return $response->withHeader('Location', $redirect);
});

$app->get('/files/[{file:.*}]', function (Request $request, Response $response, array $args) {
    $enviroment = $this->get('enviroment');
    $filepath = $enviroment->getSiteDir().'/files/'.$args['file'];
    if (!file_exists($filepath)) {
        $response = $response->withStatus(302);
        $redirect = trim($enviroment->getServer());
        return $response->withHeader('Location', $redirect);
    }

    $pathinfo = pathinfo($filepath);
    $extension = $pathinfo['extension'];
    $fileName = $pathinfo['basename'];

    $response = $response->withHeader('Content-Type', 'application/'.$extension);
    $response = $response->withHeader('Content-Disposition', sprintf('attachment; filename="%s"', $fileName));

    $body = $response->getBody();
    $body->rewind();
    $body->write(file_get_contents($filepath));

    return $response;
});

$app->get('[/{path:.*}]', function (Request $request, Response $response, array $args) {
    $reader = $this->get('reader');

    // Parse request path to determine language and requested page
    $path = new Path(
        $args['path'] ?? '',
        $reader->getDefaultLanguage(),
        $reader->getLanguages(),
        $reader->getSlugs(),
        $reader->getRedirects()
    );

    // Check if the requested path needs to redirect
    $redirect = $path->getRedirect();
    $enviroment = $this->get('enviroment');
    if (null !== $redirect) {
        // Check if internal url
        $parsedUrl = parse_url($redirect);
        if (!isset($parsedUrl['scheme'])) {
            $redirect = trim($enviroment->getServer() . '/' . $redirect, '/');
        }
        return $response->withStatus(302)->withHeader('Location', $redirect);
    }

    $documentBuilder = new DocumentBuilder($enviroment, $reader, $path);
    $componentBuilder = new ComponentBuilder($request, $enviroment, $reader, $path, $this->get('caniuse'));
    $componentBuilder->addFactory(new ComponentFactory());
    foreach ($reader->getComponentFactories() as $class) {
        $componentBuilder->addFactory(new $class());
    }
    // $sectionBuilder = new SectionBuilder(
    //     $enviroment,
    //     $reader,
    //     $componentBuilder,
    // );

    $metaBuilder = new MetaBuilder($enviroment, $reader, $path);
    $componentBuilder->addListener($metaBuilder);

    $faviconBuilder = new FaviconBuilder($enviroment, $reader);
    $componentBuilder->addListener($metaBuilder);

    $scriptBuilder = new ScriptBuilder(
        $enviroment->getBasePath(),
        (bool)$reader->get('offline')
    );
    $componentBuilder->addListener($scriptBuilder);

    $perloadBuilder = new PreloadBuilder();
    $componentBuilder->addListener($perloadBuilder);

    $page = $path->getPage();

    foreach ($reader->getSections($page, $path->getLanguage()) as $sectionData) {
        $section = $componentBuilder->build('section', $sectionData, ['type'=>'group'], $reader->get('theme.appearance') ?? 'light');
        $documentBuilder->addSection($section);
    }
    $document = $documentBuilder->getDocument();

    // Add body class TODO fix
    $bodyStyle = StyleAppearanceHelper::apply(
        \Flipsite\Utils\ArrayHelper::merge($componentBuilder->getStyle('body'), $reader->get('theme.components.body') ?? []),
        $reader->get('theme.appearance') ?? 'light'
    );
    $document->getChild('body')->addStyle($bodyStyle);

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
            $manifestLink->setAttribute('href', '/manifest.json');
            $document->getChild('head')->addChild($manifestLink);
        }
    } else {
        $document->getChild('head')->getChild('title')->setContent('OFFLINE');
    }

    $bodyHtml = $document->getChild('body')->render(2, 1);
    if (strpos($bodyHtml, 'scroll:')) {
        $componentBuilder->dispatch(new Flipsite\Components\Event('ready-script', 'scroll', file_get_contents(__DIR__.'/../js/ready.scroll.min.js')));
    }
    // if (strpos($bodyHtml, 'stuck:')) {
    //     $componentBuilder->dispatch(new Flipsite\Components\Event('ready-script', 'stuck', file_get_contents(__DIR__.'/../js/ready.stuck.js')));
    // }
    if (strpos($bodyHtml, 'enter:')) {
        $componentBuilder->dispatch(new Flipsite\Components\Event('ready-script', 'enter', file_get_contents(__DIR__.'/../js/ready.enter.min.js')));
    }
    // Add Scripts
    $document = $scriptBuilder->getDocument($document);

    // Add Analytics
    $integrations = $reader->get('integrations');
    if ($enviroment->isLive() && null !== $integrations) {
        $analyticsBuilder = new AnalyticsBuilder($integrations);
        $document = $analyticsBuilder->getDocument($document);
    }

    $response->getBody()->write($document->render());

    return $response->withHeader('Content-type', 'text/html');
})->add($cssMw)->add($svgMw)->add($offlineMw)->add($diagnosticsMw);
if ('localhost' === getenv('APP_ENV')) {
    $app->add(new FlipsiteMiddleware($container->get('reader')));
}

$errorMiddleware = $app->addErrorMiddleware(true, true, true);
$errorMiddleware->setDefaultErrorHandler(new CustomErrorHandler($app, $cssMw));

$app->run();
