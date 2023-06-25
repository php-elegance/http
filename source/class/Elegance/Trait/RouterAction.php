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
    protected static array $prefix = [];

    static function setPrefixAction(string $prefix, Closure $action, $reaction = false): void
    {
        self::$prefix[$prefix] = [$action, $reaction];
    }

    protected static function getAction($response, $isReaction = false): Closure
    {
        if (is_httpStatus($response))
            return fn () => throw new Exception('', $response);

        if (is_closure($response))
            return fn () => self::action_closure($response);

        if (is_object($response))
            return fn () => self::action_object($response);

        if (is_string($response)) {
            if (!$isReaction)
                foreach (array_keys(self::$prefix) as $prefix)
                    if (str_starts_with($response, $prefix)) {
                        list($action, $reaction) = self::$prefix[$prefix];
                        $response = substr($response, strlen($prefix));
                        if ($reaction)
                            return fn () => self::getAction($action($response), true)();
                        return fn () => $action($response);
                    }

            if (str_starts_with($response, '::'))
                return fn () => self::action_import(substr($response, 2));

            return fn () => $response;
        }

        return fn () => throw new Exception('Invalid response route', STS_INTERNAL_SERVER_ERROR);
    }

    protected static function action_import($file)
    {
        $response = Import::return($file);

        if (is_closure($response))
            return self::action_closure($response);

        if (is_object($response))
            return self::action_object($response);

        throw new Exception('Invalid route file return', STS_INTERNAL_SERVER_ERROR);
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

    protected static function action_object(Object $object)
    {
        $paramsMethod = [];

        $method = strtolower(Request::type());

        if (!method_exists($object, $method)) {
            $errCode = STS_METHOD_NOT_ALLOWED;
            throw new Exception("Method [$method] not allowed", $errCode);
        }

        $paramsMethod = self::getUseParams(new ReflectionMethod($object, $method));

        return $object->{$method}(...$paramsMethod);
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
