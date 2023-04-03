<?php

namespace Middleware;

use Closure;
use Elegance\Energize;
use Elegance\Request;
use Elegance\Response;
use Error;
use Exception;

class MdEnergize
{
    function __invoke(Closure $next)
    {
        try {
            return Energize::solve($next());
        } catch (Error | Exception $e) {
            if (Request::header('Energize-Request-Type')) {
                if ($e->getCode() == STS_REDIRECT) {
                    Response::cache(false);
                    Response::header('New-Location', $e->getMessage());
                    throw new Exception('', STS_OK);
                }
            }
            throw $e;
        }
    }
}
