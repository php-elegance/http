<?php

namespace Elegance\Trait;

use Closure;
use Elegance\File;
use Elegance\Import;
use Elegance\Request;
use Elegance\Response;
use Elegance\View;
use Exception;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionMethod;

trait RouterAction
{
    protected static function getAction($response): Closure
    {
        if (is_httpStatus($response))
            return fn () => throw new Exception('', $response);

        if (is_string($response)) {
            if (str_starts_with($response, '::'))
                return fn () => self::action_import(substr($response, 2));
            return fn () => $response;
        }

        if (is_closure($response))
            return fn () => self::action_closure($response);

        return fn () => throw new Exception('Invalid response route', STS_INTERNAL_SERVER_ERROR);
    }

    protected static function action_import($file)
    {
        $file = path($file);

        if (File::check($file)) {

            $fileEx = strtolower(File::getEx($file));

            if ($fileEx == 'php') {
                $response = (function ($__FILEPATH__, $__PARAMS__) {
                    foreach (array_keys($__PARAMS__) as $__KEY__)
                        if (!is_numeric($__KEY__))
                            $$__KEY__ = $__PARAMS__[$__KEY__];

                    $__DATA = $__PARAMS__;
                    $__TYPE = 'html';

                    ob_start();
                    $__RETURN__ = require $__FILEPATH__;
                    $__OUTPUT__ = ob_get_clean();

                    if (empty($__OUTPUT__))
                        return $__RETURN__;

                    Response::type(View::getSuportedType($__TYPE));
                    return View::renderString($__OUTPUT__, $__DATA, $__TYPE);
                })($file, Request::route());
            } else if (View::checkSuportedType($fileEx)) {
                Response::type(View::getSuportedType($fileEx));
                $content = Import::content($file);
                $response = View::renderString($content,  Request::route(), $fileEx);
            } else {
                Response::type($fileEx);
                $response = Import::content($file, Request::route());
            }
        }

        if (is_closure($response))
            return self::action_closure($response);

        if (is_object($response))
            return self::action_object($response);

        return $response;
    }

    protected static function action_closure(mixed $function)
    {
        if ($function instanceof Closure) {
            $params = self::getUseParams(new ReflectionFunction($function));
        } else {
            $params = self::getUseParams(new ReflectionMethod($function, '__invoke'));
        }
        return $function(...$params);
    }

    /** Retorna os parametros que devem ser usados em um metodo refletido */
    protected static function getUseParams(ReflectionFunctionAbstract $reflection): array
    {
        $params = [];
        $data = Request::route();

        foreach ($reflection->getParameters() as $param) {
            $name = $param->getName();
            if (isset($data[$name])) {
                $params[] = $data[$name];
            } else if ($param->isDefaultValueAvailable()) {
                $params[] = $param->getDefaultValue();
            } else {
                throw new Exception("Parameter [$name] is required", STS_INTERNAL_SERVER_ERROR);
            }
        }

        return $params;
    }
}
