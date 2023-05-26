<?php

namespace Elegance;

use Elegance\ViewRender\ViewRenderHtml;
use Error;
use Exception;

abstract class Front
{
    protected static array $head = [];
    protected static array $error = [];

    protected static ?string $layoutView = null;
    protected static bool $layoutStatic = true;

    /** Define o layout que deve ser utilizado para encapsular a resposta */
    static function layout($view, $static = true)
    {
        self::$layoutView = $view;
        self::$layoutStatic = $static;
    }

    /** Define o titulo da página no navegador */
    static function title(?string $title)
    {
        self::head('title', $title);
    }

    /** Define o favicon da página no navegador */
    static function favicon(?string $favicon)
    {
        self::head('favicon', $favicon);
    }

    /** Define o valor da tag description */
    static function description(?string $description)
    {
        self::head('description', $description);
    }

    /** Define um valor dinamico para o head */
    static function head($name, $value)
    {
        self::$head[$name] = $value;
    }

    /** Define uma rota para redirecionamento em caso de erro */
    static function error(int|string $status, ?string $route = null)
    {
        if (is_string($status)) {
            $route = $status;
            $status = 0;
        }
        self::$error[$status] = $route;
    }

    /** Resolve um conteúdo encapsulando em uma resposta front */
    static function solve($content)
    {
        if (is_httpStatus($content))
            throw new Exception('', $content);

        if (!is_stringable($content))
            return $content;

        if (Request::header('Front-Request')) {
            Response::type('json');
            Response::status(STS_OK);
            Response::content([
                'elegance' => true,
                'info' => [
                    'status' => STS_OK,
                    'error' => false,
                ],
                'data' => self::renderToArray($content),
            ]);
            Response::send();
        }

        $content = self::renderToHtml($content);
        Response::type('html');
        Response::status(STS_OK);
        Response::content($content);
        Response::send();
    }

    /** Renderiza um conteúdo dentro de uma estrutua de resposta parcial */
    protected static function renderToArray($content)
    {
        $hash = self::getLayoutHash();

        $response = [
            'head' => self::$head,
            'hash' => $hash,
            'content' => ViewRenderHtml::organizeHtml($content),
        ];

        if (Request::header('Front-Hash') != $hash)
            $response['content'] = self::renderLayout($response['content']);

        return $response;
    }

    /** Renderia um conteúdo dentro de uma estrutura de resposta completa */
    protected static function renderToHtml($content)
    {
        $content = ViewRenderHtml::organizeHtml($content);

        $content = self::renderLayout($content);
        $content = self::renderPage($content);

        return $content;
    }

    /** Renderiza o Layout da respsta */
    protected static function renderLayout($content)
    {
        $content = "<div id='front_content'>\n$content\n</div>";

        if (!is_null(self::$layoutView)) {
            $layout = View::render(self::$layoutView, ['head' => self::$head]);
            $layout = ViewRenderHtml::organizeHtml($layout);
            $layout = str_replace('[#content]', $content, $layout);
        }

        return $layout ?? $content;
    }

    protected static function renderPage($content)
    {
        $hash = self::getLayoutHash();
        $content = "<div id='front_layout' data-hash='$hash'>\n$content\n</div>";

        $view = 'library/base.html';
        if (!File::check($view))
            $view = dirname(__DIR__, 3) . "/$view";

        $page = View::render("=$view", ['head' => self::$head]);
        $page = ViewRenderHtml::organizeHtml($page);
        $page = str_replace('[#content]', $content, $page);

        return $page;
    }

    /** Retorna o hash do layout atual */
    protected static function getLayoutHash(): string
    {
        $key = self::$layoutView ?? Request::host();
        if (!self::$layoutStatic) $key .= url(true);
        return Code::on($key);
    }
}
