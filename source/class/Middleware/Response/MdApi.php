<?php

namespace Middleware\Response;

use Closure;
use Elegance\Response;
use Error;
use Exception;

class MdApi
{
    function __invoke(Closure $next)
    {
        Response::type('json');

        try {
            $response = $next();

            if (is_httpStatus($response))
                throw new Exception('', $response);

            Response::content($response, false);
            $this->encapsResponse($response);
        } catch (Exception | Error $e) {
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
            'info' => [
                'status' => $status,
                'error' => $status > 399
            ],
            'data' => []
        ];

        switch ($status) {
            case STS_REDIRECT:
                $message = !empty($message) ? url($message) : url(true);
                Response::header('location', $message);
                $response['info'] = ['to' => $message];
                break;
            default:
                $info = [];
                if (!empty($message))
                    $info = is_json($message) ? json_decode($message, true) : ['message' => $message];

                if ($status >= 500 && !env('DEV'))
                    $info = [];

                $response['info'] = !empty($info) ? $info : [];
        }

        if (env('DEV')) {
            $response['info']['file'] = $e->getFile();
            $response['info']['line'] = $e->getLine();

            Response::header('Error-File', $e->getFile());
            Response::header('Error-Line', $e->getLine());
        }

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
            'info' => [
                'status' => $status,
                'error' => $status > 399
            ],
            'data' => $content
        ];

        Response::status($status);
        Response::content($response);
    }
}
