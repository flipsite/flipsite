<?php

declare(strict_types=1);

namespace Flipsite\App;

use Flipsite\App\Middleware\CssMiddleware;
use Flipsite\Components\Document;
use Flipsite\Components\Element;
use Flipsite\Components\Pre;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Symfony\Component\Yaml\Yaml;
use Throwable;

class CustomErrorHandler
{
    private App $app;
    private CssMiddleware $cssMw;

    public function __construct(App $app, CssMiddleware $cssMw)
    {
        $this->app   = $app;
        $this->cssMw = $cssMw;
    }

    public function __invoke(Request $request, Throwable $exception) : Response
    {
        $response = $this->app->getResponseFactory()->createResponse();
        $document = new Document();
        $document->setAttribute('lang', 'en');
        $head = new Element('head');
        $head->addChild(new Element('title', true), 'title');
        $charset = new Element('meta', true, true);
        $charset->setAttribute('charset', 'utf-8');
        $head->addChild($charset);
        $viewport = new Element('meta', true, true);
        $viewport->setAttribute('name', 'viewport');
        $viewport->setAttribute('content', 'width=device-width, initial-scale=1, shrink-to-fit=no, viewport-fit=cover');
        $head->addChild($viewport);
        $document->addChild($head);
        $body = new Element('body');
        $body->addStyle([
            'fontFamily' => 'font-mono',
            'textSize'   => 'text-3.5',
            'padding'    => 'p-5',
            'maxWidth'   => 'max-w-screen-xl mx-auto',
        ]);

        $heading = new Element('h1');
        $heading->setContent('Exception');
        $heading->addStyle([
            'textSize'   => 'text-10',
            'fontWeight' => 'font-bold',
            'lineHeight' => 'leading-none',
        ]);
        $body->addChild($heading);

        $type = new Element('div');
        $type->setContent(get_class($exception));
        $type->addStyle([
            'display'   => 'block',
            'textColor' => 'text-red',
        ]);
        $body->addChild($type);

        $msg = new Element('p');
        $msg->addStyle([
            'marginY'    => 'mt-10',
            'bgColor'    => 'bg-red-100',
            'padding'    => 'p-3',
            'lineHeight' => 'leading-loose',
            'maxWidth'   => 'max-w-screen-md',
        ]);
        $parsedown = new \Parsedown();
        $md        = $parsedown->line($exception->getMessage());
        $md        = str_replace('<code>', '<code class="bg-red-200 rounded p-1 text-red-800">', $md);
        $msg->setContent($md);
        $body->addChild($msg);

        if (isset($exception->data)) {
            $pre = new Pre();
            $pre->addStyle([
            'bgColor'   => 'bg-gray-800',
            'textColor' => 'text-gray-400',
            'padding'   => 'p-3',
            'maxWidth'  => 'max-w-screen-md',
        ]);
            $md = trim(Yaml::dump($exception->data));

            if (isset($exception->attribute)) {
                $md = str_replace($exception->attribute.':', '<span class="text-white">'.$exception->attribute.':</span>', $md);
                if (is_string($exception->value)) {
                    $md = str_replace($exception->value, '<span class="text-red-400 line-through">'.$exception->value.'</span>', $md);
                }
            }

            $pre->setContent($md);
            $body->addChild($pre);
        }

        $heading = new Element('h2');
        $heading->addStyle(['marginY' => 'mt-10']);
        $heading->setContent('Trace');
        $heading->addStyle([
            'textSize'   => 'text-6',
            'fontWeight' => 'font-bold',
            'lineHeight' => 'leading-none',
        ]);
        $body->addChild($heading);

        $lines = explode("\n", $exception->getTraceAsString());
        $lines = preg_replace('/^#\d+ /', '', $lines);

        $table = new Element('table');
        $table->addStyle([
            'textSize' => 'text-3',
        ]);
        foreach ($lines as $row => $line) {
            $tr = new Element('tr');
            if ($row > 0) {
                $tr->addStyle(['textSize' => 'text-2', 'textColor' => 'text-gray-400']);
            }
            $parts = explode(':', $line);
            $td    = new Element('td');
            if (0 == $row) {
                $td->addStyle(['textColor' => 'text-red']);
            }
            $td->setContent(str_replace('/Users/Henrik/Sites/flipsite/', '', $parts[0]));
            $tr->addChild($td);

            $td = new Element('td');
            $td->setContent($parts[1] ?? '');
            $tr->addChild($td);
            $table->addChild($tr);
        }
        $body->addChild($table);

        $document->addChild($body);

        $response->getBody()->write($document->render());
        $response = $this->cssMw->getResponse($response);
        return $response;
    }
}
