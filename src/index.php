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

require_once getenv('VENDOR_DIR').'/autoload.php';
require_once 'polyfills.php';

if (!getenv('APP_BASEPATH')) {
    $basePath = str_replace('/index.php', '', $_SERVER['SCRIPT_NAME']);
    $protocol = 'on' === ($_SERVER['HTTPS'] ?? '') ? 'https' : 'http';
    putenv('APP_BASEPATH='.$basePath);
    putenv('APP_SERVER='.$protocol.'://'.$_SERVER['HTTP_HOST']);
} else {
    $protocol = 'on' === ($_SERVER['HTTPS'] ?? '') ? 'https' : 'http';
    putenv('APP_SERVER='.$protocol.'://'.$_SERVER['HTTP_HOST']);
}

$container = new Container();
$container->add('enviroment', 'Flipsite\Enviroment', true);
$container->add('reader', 'Flipsite\Data\Reader', true)->addArgument('enviroment');
AppFactory::setContainer($container);
$app = AppFactory::create();
$app->addBodyParsingMiddleware();
$app->setBasePath(getenv('APP_BASEPATH'));

$cssMw         = new CssMiddleware($container->get('reader')->get('theme'));
$diagnosticsMw = new DiagnosticsMiddleware();
$expiresMw     = new ExpiresMiddleware(365 * 86440);
$offlineMw     = new OfflineMiddleware($container->get('enviroment'), $container->get('reader'));
$svgMw         = new SvgMiddleware($container->get('enviroment'));

$app->get('/img[/{file:.*}]', function (Request $request, Response $response, array $args) {
    $enviroment = $this->get('enviroment');
    $handler = new ImageHandler(
        $enviroment->getImageSources(),
        $enviroment->getImgDir()
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

$app->get('/robots.txt', function (Request $request, Response $response) {
    $enviroment = $this->get('enviroment');
    $robots = new Robots($enviroment->isLive(), $enviroment->getServer());
    $response->getBody()->write((string) $robots);
    return $response->withHeader('Content-type', 'text/plain');
});

$app->get('/api/pages', function (Request $request, Response $response) {
    $reader = $this->get('reader');
    $pages = array_keys($reader->get('pages'));
    $response->getBody()->write(json_encode($pages));
    return $response->withHeader('Content-Type', 'application/json');
});


$app->post('/api/sections/{page}', function (Request $request, Response $response, array $args) {
    $page = str_replace('-', '/', $args['page']);
    $body = $request->getParsedBody();

    $enviroment = $this->get('enviroment');
    $reader = $this->get('reader');
    $pages = array_keys($reader->get('pages'));
    $siteFilePath = $enviroment->getSiteDir().'/site.yaml';
    $site = Symfony\Component\Yaml\Yaml::parseFile($siteFilePath);
    if (in_array($page, ['before','after'])) {
        if (!isset($site[$page])) {
            $site[$page] = [$body];
        } else {
            $site[$page][] = $body;
        }
    } else {
        if (!isset($site['pages'][$page])) {
            $site['pages'][$page] = [$body];
        } else {
            $site['pages'][$page][] = $body;
        }
    }
    file_put_contents($siteFilePath, Flipsite\Utils\YamlDumper::dump($site, 8, 2, Symfony\Component\Yaml\Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK));
    return $response->withStatus(202);
});

$app->post('/api/theme/style/{style}', function (Request $request, Response $response, array $args) {
    $style = $args['style'];
    $body = $request->getParsedBody();
    $enviroment = $this->get('enviroment');
    $reader = $this->get('reader');
    $themeFilePath = $enviroment->getSiteDir().'/theme.yaml';
    $theme = Symfony\Component\Yaml\Yaml::parseFile($themeFilePath);
    if (!isset($theme['style'][$style])) {
        $theme['style'][$style] = $body;
        file_put_contents($themeFilePath, Flipsite\Utils\YamlDumper::dump($theme, 8, 2, Symfony\Component\Yaml\Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK));
    }
    return $response->withStatus(200);
});

$app->get('[/{path:.*}]', function (Request $request, Response $response, array $args) {
    $reader = $this->get('reader');

    // Parse request path to determine language and requested page
    $path = new Path(
        $args['path'] ?? '',
        $reader->getDefaultLanguage(),
        $reader->getLanguages(),
        $reader->getSlugs()
    );

    // Check if the requested path needs to redirect
    $redirect = $path->getRedirect();
    $enviroment = $this->get('enviroment');
    if (null !== $redirect) {
        $response = $response->withStatus(302);
        $redirect = trim($enviroment->getServer().'/'.$redirect, '/');
        return $response->withHeader('Location', $redirect);
    }

    $documentBuilder = new DocumentBuilder($enviroment, $reader, $path);
    $componentBuilder = new ComponentBuilder($enviroment, $reader, $path);
    $componentBuilder->addFactory(new ComponentFactory());
    foreach ($reader->getComponentFactories() as $class) {
        $componentBuilder->addFactory(new $class($componentBuilder));
    }

    $sectionBuilder = new SectionBuilder(
        $enviroment,
        $componentBuilder,
        $reader->get('theme')
    );

    $metaBuilder = new MetaBuilder($enviroment, $reader, $path);
    $componentBuilder->addListener($metaBuilder);

    $faviconBuilder = new FaviconBuilder($enviroment, $reader);
    $componentBuilder->addListener($metaBuilder);

    $scriptBuilder = new ScriptBuilder();
    $componentBuilder->addListener($scriptBuilder);

    $page = $path->getPage();
    $documentBuilder->addLayout($reader->getLayout($page));
    foreach ($reader->getSections($page, $path->getLanguage()) as $sectionData) {
        $area = $sectionData['area'] ?? 'default';
        unset($sectionData['area']);
        $section = $sectionBuilder->getSection($sectionData);
        $documentBuilder->addSection($section, $area);
    }
    $document = $documentBuilder->getDocument();

    // Add body class
    $bodyStyle = StyleAppearanceHelper::apply(
        $reader->get('theme.components.body') ?? [],
        $reader->get('theme.appearance') ?? 'light'
    );
    $document->getChild('body')->addStyle($bodyStyle);


    // Add Meta
    $document = $metaBuilder->getDocument($document);

    // Add Favicon
    $document = $faviconBuilder->getDocument($document);

    // Add Webfonts
    $fonts = $reader->get('theme.fonts');
    if (null !== $fonts) {
        $fontBuilder = new FontBuilder($fonts);
        $document = $fontBuilder->getDocument($document);
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
