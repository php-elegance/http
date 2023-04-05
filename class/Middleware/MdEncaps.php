<?php

namespace Middleware;

use Closure;
use Elegance\Response;
use Error;
use Exception;

class MdEncaps
{
    function __invoke(Closure $next)
    {
        try {
            $response = $next();

            if (is_stringable($response) && !is_json($response))
                Response::content(Response::getContent() ?? $response);
            else {
                if (is_httpStatus($response))
                    throw new Exception('', $response);
                $this->encapsResponse($response);
            }
        } catch (Error | Exception $e) {
            $this->encapsCatch($e);
        }
        Response::send();
    }

    /** Encapsula um erro ou exception dentro de um json de resposta APIs */
    protected function encapsCatch(Error | Exception $e)
    {
        $status = $e->getCode();
        $message = $e->getMessage();

        if (!is_httpStatus($status))
            $status = !is_class($e, Error::class) ? STS_BAD_REQUEST : STS_INTERNAL_SERVER_ERROR;

        $response = [
            'elegance' => true,
            'status' => $status,
            'detail' => [],
            'data' => []
        ];

        switch ($status) {
            case STS_REDIRECT:
                $message = !empty($message) ? url($message) : url(true);
                Response::header('location', $message);
                $response['detail'] = ['to' => $message];
                break;
            default:
                $detail = [];
                if (!empty($message))
                    $detail = is_json($message) ? json_decode($message, true) : ['message' => $message];

                if ($status >= 500 && !env('DEV'))
                    $detail = [];

                $response['detail'] = !empty($detail) ? $detail : [];
        }

        if (env('DEV')) {
            $response['detail']['file'] = $e->getFile();
            $response['detail']['line'] = $e->getLine();
        }

        Response::type('json');
        Response::status($status);
        Response::content($response);

        Response::cache(false);
    }

    /** Encapsula o conteúdo da resposta dentro de um json de resposta API */
    protected function encapsResponse($content)
    {
        $status = Response::getStatus();
        $content = $content ?? Response::getContent();
        $content = is_json($content) ? json_decode($content) : $content;

        if (!is_httpStatus($status))
            $status = STS_OK;

        $response = [
            'elegance' => true,
            'status' => $status,
            'detail' => [],
            'data' => $content
        ];

        Response::type('json');
        Response::status($status);
        Response::content($response);
    }
}
