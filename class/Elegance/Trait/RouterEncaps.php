<?php

namespace Elegance\Trait;

use Elegance\Response;
use Error;
use Exception;

trait RouterEncaps
{
    /** Encapsula um erro ou exception dentro de um json de resposta APIs */
    static function encapsError(Error | Exception $e)
    {
        $status = $e->getCode();
        $message = $e->getMessage();

        if (!is_httpStatus($status))
            $status = !is_class($e, Error::class) ? STS_BAD_REQUEST : STS_INTERNAL_SERVER_ERROR;

        $info = is_json($message) ? json_decode($message, true) : ['message' => $message];

        $response = [
            'elegance' => true,
            'status' => $status,
            'error' => $status > 299,
            'type' => $info['type'] ?? null,
            'origin' => $info['origin'] ?? null,
            'data' => [
                'message' => $info['message'] ?? null,
                'description' => $info['description'] ?? null,
            ]
        ];

        if (is_blank($response['type'])) $response['type'] = match ($status) {
            200, 201, 204 => '_success',
            303, => '_redirect',
            400, 401, 403, 404, 405, => '_default',
            500, 501, 503 => '_error',
            default => '_unknown'
        };

        if ($response['type'] == '_error') $response['data']['message'] = null;

        if (is_blank($response['origin'])) $response['origin'] = '_system';

        if (is_blank($response['data']['message'])) $response['data']['message'] = null;

        if (is_blank($response['data']['description'])) $response['data']['description'] = null;

        if (env('DEV')) $response['dbug'] = [
            'code' => $e->getCode(),
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ];

        Response::type('json');
        Response::status($status);
        Response::cache(false);
        Response::content($response);
    }

    /** Encapsula o conteúdo da resposta dentro de um json de resposta API */
    static function encapsResponse($content)
    {
        $status = Response::getStatus();
        $content = $content ?? Response::getContent();
        $content = is_json($content) ? json_decode($content) : $content;

        if (!is_httpStatus($status))
            $status = STS_OK;

        $response = [
            'elegance' => true,
            'status' => $status,
            'error' => $status > 299,
            'type' => $info['type'] ?? null,
            'origin' => '_response',
            'data' => $content
        ];

        if (is_blank($response['type'])) $response['type'] = match ($status) {
            200, 201, 204 => '_success',
            303, => '_redirect',
            400, 401, 403, 404, 405, => '_default',
            500, 501, 503 => '_error',
            default => '_unknown'
        };

        Response::type('json');
        Response::status($status);
        Response::cache(false);
        Response::content($response);
    }
}
