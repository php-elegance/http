<?php

namespace Elegance;

abstract class Response
{
    protected static array $header = [];

    protected static ?int $status = null;
    protected static ?string $type = null;
    protected static mixed $content = null;

    protected static null|int|bool $cache = null;

    protected static bool $download = false;
    protected static ?string $downloadName = null;

    /** Define o status HTTP da resposta */
    static function status(?int $status, bool $replace = true)
    {
        self::$status = $replace ? $status : (self::$status ?? $status);
    }

    /** Define um cabeçalho para a resposta */
    static function header(string|array $name, ?string $value = null)
    {
        if (is_array($name)) {
            foreach ($name as $n => $v)
                self::header($n, $v);
        } else {
            self::$header[$name] = $value;
        }
    }

    /** Define o contentType da resposta */
    static function type(?string $type, bool $replace = true)
    {
        if ($type) {
            $type = trim($type, '.');
            $type = strtolower($type);
            $type = EX_MIMETYPE[$type] ?? $type;
        }

        self::$type = $replace ? $type : (self::$type ?? $type);
    }

    /** Define o conteúdo da resposta */
    static function content(mixed $content, bool $replace = true)
    {
        self::$content = $replace ? $content : (self::$content ?? $content);
    }

    /** Define se o arquivo deve ser armazenado em cache */
    static function cache(null|bool|int $time)
    {
        self::$cache = $time;
    }

    /** Define se o navegador deve fazer download da resposta */
    static function download(null|bool|string $download)
    {
        if (is_string($download)) {
            self::$downloadName = $download;
            $download = true;
        }
        self::$download = boolval($download);
    }

    /** Envia a resposta ao navegador do cliente */
    static function send()
    {
        $content = self::getMontedContent();
        $headers = self::getMontedHeders();

        http_response_code(self::$status ?? STS_OK);

        foreach ($headers as $name => $value)
            header("$name: $value");

        die($content);
    }

    #==| Mount |==#

    /** Retorna conteúdo da resposta */
    protected static function getMontedContent(): string
    {
        return is_array(self::$content) ? json_encode(self::$content) : strval(self::$content);
    }

    /** Retorna cabeçalhos de resposta */
    protected static function getMontedHeders(): array
    {
        return [
            ...self::$header,
            ...self::getMontedHeaderCache(),
            ...self::getMontedHeaderType(),
            ...self::getMontedHeaderDownload()
        ];
    }

    /** Retorna cabeçalhos de cache */
    protected static function getMontedHeaderCache(): array
    {
        $headerCache = [];
        $cacheType = array_flip(EX_MIMETYPE)[self::$type] ?? null;
        $cacheTime = self::$cache;

        if (is_null($cacheTime))
            $cacheTime = env(strtoupper("CACHE_$cacheType")) ?? env("CACHE");

        if ($cacheTime === true)
            $cacheTime = env("CACHE");

        if (!is_null($cacheTime)) {
            $cacheTime = intval($cacheTime);
            if ($cacheTime) {
                $cacheTime = $cacheTime * 60 * 60;
                $headerCache['Pragma'] = 'public';
                $headerCache['Cache-Control'] = 'max-age=' . $cacheTime;
                $headerCache['Expires'] = gmdate('D, d M Y H:i:s', time() + $cacheTime) . ' GMT';
            } else {
                $headerCache['Pragma'] = 'no-cache';
                $headerCache['Cache-Control'] = 'no-cache, no-store, must-revalidat';
                $headerCache['Expires'] = '0';
            }
        }

        $headerCache['Elegance-Cache'] = $cacheTime ? $cacheTime : 'false';

        return $headerCache ?? [];
    }

    /** Retorna cabeçalhos de tipo de conteúdo */
    protected static function getMontedHeaderType(): array
    {
        if (is_array(self::$content) || is_json(self::$content))
            self::type('json');

        $type = self::$type ?? EX_MIMETYPE['html'];

        return ['Content-Type' => "$type; charset=utf-8"];
    }

    /** Retorna cabeçalhos de download */
    protected static function getMontedHeaderDownload(): array
    {
        $headerDownload = [];
        if (self::$download) {
            $ex = array_flip(EX_MIMETYPE)[self::$type] ?? 'download';
            $fileName = self::$downloadName ?? 'download';
            File::ensure_extension($fileName, $ex);
            $headerDownload['Content-Disposition'] = "attachment; filename=$fileName";
        }
        return $headerDownload;
    }
}
