<?php

namespace Middleware\Response;

use Closure;
use Elegance\Cif;
use Elegance\Front;
use Elegance\Request;
use Elegance\Response;
use Error;
use Exception;

class MdFront
{
    function __invoke(Closure $next)
    {
        try {
            return Front::solve($next());
        } catch (Exception | Error $e) {
            $url = match (true) {
                $e->getCode() == STS_REDIRECT => $e->getMessage(),
                IS_GET && env('REDIRECT_ERROR_' . $e->getCode()) => url(env('REDIRECT_ERROR_' . $e->getCode())),
                IS_GET && env('REDIRECT_ERROR') => url(env('REDIRECT_ERROR')),
                default => false
            };

            if ($url && $url != url(true)) {

                if ($e->getCode() != STS_REDIRECT) {
                    $info = [
                        'code' => $e->getCode(),
                        'url' => url(true)
                    ];
                    $url = url($url, ['info' => Cif::on($info, 'E')]);
                }

                if (Request::header('Front-Request')) {
                    Response::cache(false);
                    Response::header('Front-Location', $url);
                    throw new Exception('', STS_OK);
                }
                redirect($url);
            }

            throw $e;
        }
    }
}
