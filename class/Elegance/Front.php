<?php

namespace Elegance;

use Elegance\Trait\FrontHead;
use Elegance\Trait\FrontRender;

abstract class Front
{
    use FrontRender;
    use FrontHead;

    /** Resolve o um conteúdo como uma página HTML */
    static function solve($content)
    {
        if (!is_stringable($content))
            return $content;

        if (Request::header('Front-Request-Type') == 'link') {
            Response::type('json');
            Response::status(STS_OK);
            Response::content([
                'elegance' => true,
                'status' => STS_OK,
                'error' => false,
                'detail' => null,
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

    /** Define uma view que deve ser utilizada como página da resposta */
    static function usePage(?string $view)
    {
        self::$page = $view;
    }

    /** Define uma view que deve ser utilizada como layout da resposta */
    static function useLayout(?string $view)
    {
        self::$layout = $view;
    }
}
