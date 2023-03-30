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
    static function status(?int $status)
    {
        self::$status = $status ?? self::$status;
    }

    /** Define variaveis do cabeçalho da resposta */
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
    static function type(?string $type)
    {
        if ($type) {
            $type = trim($type, '.');
            $type = strtolower($type);
            $type = EX_MIMETYPE[$type] ?? $type;

            self::$type = $type;
        }
    }

    /** Define o conteúdo da resposta */
    static function content(mixed $content)
    {
        self::$content = $content;
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

    /** Retorna o status HTTP da resposta */
    static function getStatus(): ?int
    {
        return self::$status;
    }

    /** Retorna um ou todos os cabeçalhos da resposta */
    static function getHeader(?string $name = null): string|array
    {
        if (!is_null($name))
            return self::$header[$name] ?? null;

        return self::$header;
    }

    /** Retorna o contentType da resposta */
    static function getType(): ?string
    {
        return self::$type;
    }

    /** Retorna o conteúdo da resposta */
    static function getContent(): mixed
    {
        return self::$content;
    }

    /** Envia a resposta ao navegador do cliente */
    static function send()
    {
        $content = self::getMontedContent();
        $headers = self::getMontedHeders();

        http_response_code($status ?? self::$status ?? STS_OK);

        foreach ($headers as $name => $value)
            header("$name: $value");

        die($content);
    }

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
            $cacheTime = env(strtoupper("RESPONSE_CACHE_$cacheType")) ?? env("RESPONSE_CACHE") ?? null;

        if ($cacheTime === true)
            $cacheTime = env("RESPONSE_CACHE") ?? null;

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

        return $headerCache ?? [];
    }

    /** Retorna cabeçalhos de tipo de conteúdo */
    protected static function getMontedHeaderType(): array
    {
        if (!self::$type && is_string(self::$content) && !is_json(self::$content))
            self::type('html');

        $type = self::$type ?? EX_MIMETYPE['json'];

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
