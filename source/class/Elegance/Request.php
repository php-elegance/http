<?php

namespace Elegance;

abstract class Request
{
    protected static ?string $type = null;
    protected static ?array $header = null;
    protected static ?bool $ssl = null;
    protected static ?string $host = null;
    protected static ?array $path = null;
    protected static ?array $query = null;
    protected static ?array $data = null;
    protected static array $route = [];
    protected static ?array $file = null;

    /** Retorna/Compara o tipo da requisição atual (GET, POST, PUT, DELETE, OPTIONS,) */
    static function type(): string
    {
        self::$type = self::$type ?? self::current_type();

        if (func_num_args())
            return self::$type == strtoupper(func_get_arg(0));

        return self::$type;
    }

    /** Retorna um ou todos os parametros header da requisição atual */
    static function header(): mixed
    {
        self::$header = self::$header ?? self::current_header();

        if (func_num_args())
            return self::$header[func_get_arg(0)] ?? null;

        return self::$header;
    }

    /** Retorna/Compara o status de utilização SSL da requisição atual */
    static function ssl(): bool
    {
        self::$ssl = self::$ssl ?? self::current_ssl();

        if (func_num_args())
            return self::$ssl == func_get_arg(0);

        return self::$ssl;
    }

    /** Retorna o host da requisiçaõ atual */
    static function host(): string
    {
        self::$host = self::$host ?? self::current_host();
        return self::$host;
    }

    /** Retorna ou o todos os caminhos da URI da requisição atual */
    static function path(): array|string
    {
        self::$path = self::$path ?? self::current_path();

        if (func_num_args())
            return self::$path[func_get_arg(0)] ?? null;

        return self::$path;
    }

    /** Retorna ou o todos os parametros passados via query na requisição autal */
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

    /** Retorna um ou todos os dados enviados via rota para a requisição atual */
    static function route(): mixed
    {
        if (func_num_args())
            return self::$route[func_get_arg(0)] ?? null;

        return self::$route;
    }

    /** Retorna um o todos os arquivos enviados na requisição atual */
    static function file(): array
    {
        self::$file = self::$file ?? self::current_file();

        if (func_num_args())
            return self::$file[func_get_arg(0)] ?? [];

        return self::$file;
    }

    #==| SET |==#

    /** Define o valor de um parametro header da requisição atual */
    static function set_header(string|int $name, mixed $value): void
    {
        self::$header = self::$header ?? self::current_header();
        self::$header[$name] = $value;
    }

    /** Define o valor de um parametro query da requisição atual */
    static function set_query(string|int $name, mixed $value): void
    {
        self::$query = self::$query ?? self::current_query();
        self::$query[$name] = $value;
    }

    /** Define o valor de um parametro do corpo da requisição atual */
    static function set_data(string|int $name, mixed $value): void
    {
        self::$data = self::$data ?? self::current_data();
        self::$data[$name] = $value;
    }

    /** Define o valor de um parametro de rota da requisição atual */
    static function set_route(string|int $name, mixed $value): void
    {
        self::$route[$name] = $value;
    }

    #==| LOAD |==#

    protected static function current_header(): array
    {
        return IS_TERMINAL ? [] : getallheaders();
    }

    protected static function current_type(): string
    {
        return match (true) {
            IS_TERMINAL => 'TERMINAL',
            IS_GET => 'GET',
            IS_POST => 'POST',
            IS_PUT => 'PUT',
            IS_DELETE => 'DELETE',
            IS_OPTIONS => 'OPTIONS',
            default => 'UNDEFINED',
        };
    }

    protected static function current_ssl(): bool
    {
        return env('FORCE_SSL') ?? strtolower($_SERVER['HTTPS'] ?? '') == 'on';
    }

    protected static function current_host(): string
    {
        return $_SERVER['HTTP_HOST'] ?? 'undefined';
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
        $query = $_SERVER['REQUEST_URI'];
        $query = parse_url($query)['query'] ?? '';
        parse_str($query, $query);

        $query = array_map(fn ($v) => urlencode($v), $query);

        return $query;
    }

    protected static function current_data(): array
    {
        if (IS_GET) {
            $data = [];
            $inputData = file_get_contents('php://input');

            if (is_json($inputData))
                $data = json_decode($inputData, true);

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
