<?php

namespace Middleware;

use Closure;
use Elegance\Front;

class MdFront
{
    function __invoke(Closure $next)
    {
        return Front::resolve($next());
    }
}
