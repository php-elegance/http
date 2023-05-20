<?php

namespace Elegance\Trait;

use Elegance\Request;

trait ControllerApi
{
    function __invoke()
    {
        return $this->{strtolower(Request::type())}();
    }

    function get()
    {
        return STS_METHOD_NOT_ALLOWED;
    }

    function post()
    {
        return STS_METHOD_NOT_ALLOWED;
    }

    function put()
    {
        return STS_METHOD_NOT_ALLOWED;
    }

    function delete()
    {
        return STS_METHOD_NOT_ALLOWED;
    }
}
