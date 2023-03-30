<?php

namespace Elegance;

abstract class Request
{
    protected static $method;

    protected static $header;

    protected static $ssl;
    protected static $host;
    protected static $port;

    protected static $path;

    protected static $query;
    protected static $data;

    protected static $files;

    /** Retorna o metodo interpretado da requisição atual */
    static function method(): string
    {
        self::$method = self::$method ?? self::current_method();
        return self::$method;
    }

    /** Retorna um ou todos os cabeçalhos da requisição atual */
    static function header(): mixed
    {
        self::$header = self::$header ?? self::current_header();

        if (func_num_args())
            return self::$header[func_get_arg(0)] ?? null;

        return self::$header;
    }

    /** Verifica se a requisição atual utiliza HTTPS */
    static function ssl(): bool
    {
        self::$ssl = self::$ssl ?? self::current_ssl();

        return self::$ssl;
    }

    /** Retorna o host usado na requisição atual */
    static function host(): string
    {
        self::$host = self::$host ?? self::current_host();

        return self::$host;
    }

    /** Retorna a porta usado na requisição atual */
    static function port(): string
    {
        self::$port = self::$port ?? self::current_port();

        return self::$port;
    }

    /** Retorna um ou todos os caminhos da URI da requisição atual */
    static function path(): mixed
    {
        self::$path = self::$path ?? self::current_path();

        if (func_num_args())
            return self::$path[func_get_arg(0)] ?? null;

        return self::$path;
    }

    /** Retorna um ou todos os dados passados na QUERY GET da requisição atual */
    static function query(): mixed
    {
        self::$query = self::$query ?? self::current_query();

        if (func_num_args() == 1)
            return self::$query[func_get_arg(0)] ?? null;

        return self::$query;
    }

    /** Retorna um ou todos os dados enviados no corpo da requisição atual */
    static function data(): mixed
    {
        self::$data = self::$data ?? self::current_data();

        if (func_num_args())
            return self::$data[func_get_arg(0)] ?? null;

        return self::$data;
    }

    /** Retorna um o todos os arquivos enviados na requisição atual */
    static function file(): array
    {
        self::$files = self::$files ?? self::current_file();

        if (func_num_args())
            return self::$files[func_get_arg(0)] ?? [];

        return self::$files;
    }

    /** Define/Altera um cabeçalho da requisição atual */
    static function setHeader(string $name, mixed $value): void
    {
        self::$header = self::$header ?? self::current_header();
        self::$header[$name] = $value;
    }

    /** Define/Altera um dos dados passados via QUERY GET na requisiçaõ atual */
    static function setQuery(string $name, mixed $value): void
    {
        self::$query = self::$query ?? self::current_query();
        self::$query[$name] = $value;
    }

    /** Define/Altera um  dos dados enviados no corpo da requisição atual */
    static function setData(string $name, mixed $value): void
    {
        self::$data = self::$data ?? self::current_data();
        self::$data[$name] = $value;
    }

    protected static function current_method(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'TERMINAL');
    }

    protected static function current_header(): array
    {
        return IS_TERMINAL ? [] : getallheaders();
    }

    protected static function current_ssl(): bool
    {
        return boolval(env('FORCE_SSL') ?? strtolower($_SERVER['HTTPS'] ?? '') == 'on');
    }

    protected static function current_host(): string
    {
        $server = $_SERVER['HTTP_HOST'] ?? '';
        $server = explode(':', $server);
        return array_shift($server);
    }

    protected static function current_port(): string
    {
        return $_SERVER['SERVER_PORT'] ?? '';
    }

    protected static function current_path(): array
    {
        if (!IS_TERMINAL) {
            $path = urldecode($_SERVER['REQUEST_URI']);
            $path = explode('?', $path);
            $path = array_shift($path);
            $path = trim($path, '/');
            $path = explode('/', $path);

            $path = array_filter($path, fn ($path) => !is_blank($path));
        }
        return $path ?? [];
    }

    protected static function current_query(): array
    {
        if (!IS_TERMINAL) {
            $query = $_SERVER['REQUEST_URI'];
            $query = parse_url($query)['query'] ?? '';
            parse_str($query, $query);
        }

        return $query ?? [];
    }

    protected static function current_data(): array
    {
        if (IS_GET) {
            $data = [];
            $inputData = file_get_contents('php://input');
            if (is_json($inputData))
                $data = json_decode($inputData, true);
            else
                parse_str($inputData, $data);

            if (!is_blank($data))
                return $data;

            return self::query();
        }

        if (IS_POST && !empty($_POST))
            return $_POST;

        $inputData = file_get_contents('php://input');
        if (is_json($inputData)) return json_decode($inputData, true);
        parse_str($inputData, $data);
        return $data;
    }

    protected static function current_file(): array
    {
        $files = [];

        foreach ($_FILES as $name => $file) {
            if (is_array($file['error'])) {
                for ($i = 0; $i < count($file['error']); $i++) {
                    $files[$name][] = [
                        'name' => $file['name'][$i],
                        'full_path' => $file['full_path'][$i],
                        'type' => $file['type'][$i],
                        'tmp_name' => $file['tmp_name'][$i],
                        'error' => $file['error'][$i],
                        'size' => $file['size'][$i],
                    ];
                }
            } else {
                $files[$name][] = [
                    'name' => $file['name'],
                    'full_path' => $file['full_path'],
                    'type' => $file['type'],
                    'tmp_name' => $file['tmp_name'],
                    'error' => $file['error'],
                    'size' => $file['size'],
                ];
            }
        }

        return $files;
    }
}
