<?php

namespace Elegance\Trait;

use Elegance\Response;
use Error;
use Exception;

trait RouterEncaps
{
    /** Encapsula um erro ou exception dentro de um json de resposta APIs */
    static function encapsCatch(Error | Exception $e)
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

        Response::content($response);

        Response::status($status);
        Response::type('json');
        Response::cache(false);
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
            'detail' => [],
            'data' => $content
        ];

        Response::type('json');
        Response::status($status);
        Response::content($response);
    }
}



/**
 * elegance: Se a resposta foi encapsulada por um backend elegance
 * status: Stauts HTTP da resposta
 * error: Se a resposta é referente a um erro (status > 399)
 * type: Tipo de respota (default,input,render,redirect,error)
 * detail: array com detahes da resposta dependendo do tipo
 *  - DEFAULT
 *      - message: Mensagem da resposta
 *      - description: Descrição mais detalhada da resposta
 *  - INPUT
 *      - field: Nome do campo que enviou a resposta
 *      - message: Mesagem da resposta
 *      - description: Descrição mais detalhada da resposta
 *  - RENDER
 *      - NULL
 *  - REDIRECT
 *      - from: URL do direcionamento
 *  - ERROR
 *      - message: Mensagem da resposta (apenas DEV)
 *      - description: Descrição mais detalhada da resposta (apenas DEV)
 * 
 * 
 */
