<?php

namespace Elegance\Trait;

use Closure;
use Elegance\Import;
use Elegance\Request;
use Exception;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionMethod;

trait RouterAction
{
    protected static function getAction($response): callable
    {
        if (is_string($response)) {
            if (str_starts_with($response, ':import:'))
                return fn () => self::action_import(substr($response, 8));

            return fn () => self::action_controller($response);
        }

        if (is_closure($response))
            return fn () => self::action_closure($response);

        if (is_object($response))
            return fn () => self::action_object($response);

        throw new Exception('Invalid response route', STS_INTERNAL_SERVER_ERROR);
    }

    protected static function action_import($file)
    {
        $action = Import::return($file);

        $action = self::getAction($action);

        return $action();
    }

    protected static function action_closure(closure $function)
    {
        if ($function instanceof Closure) {
            $params = self::getUseParams(new ReflectionFunction($function));
        } else {
            $params = self::getUseParams(new ReflectionMethod($function, '__invoke'));
        }
        return $function(...$params);
    }

    protected static function action_object($object, string $reciedMethod = '')
    {
        $paramsMethod = [];

        $method = empty($reciedMethod) ? strtolower(Request::method()) : $reciedMethod;

        if (!method_exists($object, $method)) {
            $errCode = empty($reciedMethod) ? STS_METHOD_NOT_ALLOWED : STS_INTERNAL_SERVER_ERROR;
            throw new Exception("Method [$method] not allowed", $errCode);
        }

        $paramsMethod = self::getUseParams(new ReflectionMethod($object, $method));

        return $object->{$method}(...$paramsMethod);
    }

    protected static function action_controller(string $response)
    {
        $class = $response;

        if (str_starts_with($class, '@'))
            $class = 'controller.' . substr($class, 1);

        list($class, $method) = explode(':', "$class:");
        $class = explode('.', $class);
        $class = array_map(fn ($value) => ucfirst($value), $class);
        $class = implode('\\', $class);
        $class = trim($class, '\\');

        if (!class_exists($class))
            throw new Exception("Class [$response] not found", STS_INTERNAL_SERVER_ERROR);

        $paramsConstruct = [];

        if (method_exists($class, '__construct'))
            $paramsConstruct = self::getUseParams(new ReflectionMethod($class, '__construct'));

        $object = new $class(...$paramsConstruct);

        return self::action_object($object, $method);
    }

    /** Retorna os parametros que devem ser usados em um metodo refletido */
    protected static function getUseParams(ReflectionFunctionAbstract $reflection): array
    {
        $params = [];
        $data = self::data();

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
