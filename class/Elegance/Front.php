<?php

namespace Elegance;

use Elegance\Trait\FrontHead;
use Elegance\Trait\FrontRender;

abstract class Front
{
    use FrontHead;
    use FrontRender;

    /** Resolve o frontend enviando o conteúdo HTML ou retornando os dados JSON */
    static function resolve($content)
    {
        switch (Request::header('Front-Request')) {
            case 'link':
                return self::renderToArray($content);
                break;
            case 'api';
            case 'submit';
                return $content;
            default:
                if (!is_stringable($content)) return $content;
                $content = self::renderToHtml($content);
                Response::type('html');
                Response::status(STS_OK);
                Response::content($content);
                Response::send();
        }
    }

    /** Envia um comando de redirecionamento ao frontend */
    static function redirect()
    {
        if (!Request::header('Front-Request'))
            redirect(...func_get_args());
        $url = url(...func_get_args());
        return "<script>front.action.redirect('$url')</script>";
    }

    /** Envia um comando de carregamento de uma URL ao frontend */
    static function link()
    {
        if (!Request::header('Front-Request'))
            redirect(...func_get_args());
        $url = url(...func_get_args());
        return "<script>front.action.link('$url')</script>";
    }
}
