<?php

namespace Elegance;

use Exception;

abstract class Assets
{
    /** Envia um arquivo assets como resposta da requisição */
    static function send(string $path, array $allowTypes = []): never
    {
        self::loadResponse($path, $allowTypes);
        Response::send();
    }

    /** Realiza o download de um arquivo assets como resposta da requisição */
    static function download(string $path, array $allowTypes = []): never
    {
        self::loadResponse($path, $allowTypes);
        Response::download(true);
        Response::send();
    }

    /** Carrega um arquivo na resposta da aplicação */
    static function load(string $path, array $allowTypes = []): void
    {
        self::loadResponse($path, $allowTypes);
    }

    /** Retorna o ResponseFile do arquivo */
    protected static function loadResponse(string $path, array $allowTypes): void
    {
        $path = path($path);

        if (!File::check($path) || !self::checkAllowType($path, $allowTypes))
            throw new Exception("file not found", STS_NOT_FOUND);

        Response::content(Import::content($path));
        Response::type(File::getEx($path));
        Response::download(File::getOnly($path));
        Response::download(false);
        Response::status(STS_OK);
    }

    /** Verifica se o arquivo é de alguma extensão permitida */
    protected static function checkAllowType($path, $allowTypes)
    {
        if (!empty($allowTypes)) {
            $ex = explode('.', $path);
            $ex = array_pop($ex);
            $ex = strtolower($ex);

            return in_array($ex, $allowTypes);
        }
        return true;
    }
}
