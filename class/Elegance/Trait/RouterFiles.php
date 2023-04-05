<?php

namespace Elegance\Trait;

use Elegance\File;
use Elegance\Response;

trait RouterFiles
{
    protected static $friendly = [];

    protected static $stringResponse = [];


    /** Regrista um tipo de arquivo para ser rotado de forma amigavel (sem extensão) */
    static function exFriendlyRoute(string $ex, $friendlyRoute = true)
    {
        self::$friendly[strtolower($ex)] = $friendlyRoute;
    }

    /** Regirstra um tipo de reposta para conteúdo stirng em arquivo de um tipo */
    static function exStringResponseType(string $ex, ?string $stringResponse = null)
    {
        self::$stringResponse[strtolower($ex)] = $stringResponse;
    }

    /** Prepara a resposta com o tipo padrão para resposta de texto */
    protected static function setDefaultResponsetype($ex)
    {
        if (!Response::getType())
            Response::type(self::$stringResponse[$ex] ?? $ex);
    }

    /** Remove a extensão do arquivo caso estejá registada como amigavel */
    protected static function getFriendlyRoute($route): string
    {
        $ex = File::getEx($route);

        if (self::$friendly[$ex] ?? false) {
            $route = substr($route, 0, num_negative(strlen(".$ex")));
            if (str_starts_with($route, '_index'))
                $route = substr($route, 6);
        }

        return $route;
    }
}
