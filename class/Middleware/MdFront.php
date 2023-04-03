<?php

namespace Middleware;

use Closure;
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
        } catch (Error | Exception $e) {
            if (Request::header('Front-Request-Type')) {
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
