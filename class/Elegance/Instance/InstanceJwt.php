<?php

namespace Elegance\Instance;

class InstanceJwt
{
    protected string $pass;

    function __construct(?string $pass = null)
    {
        $this->pass = $pass ?? env('JWT');
    }

    /** Retorna o token JWT  */
    function on(mixed $payload): string
    {
        $header = json_encode([
            "alg" => "HS256",
            "typ" => "JWT"
        ]);

        $payload = json_encode($payload);

        $header_payload = $this->base64url_encode($header) . '.' .
            $this->base64url_encode($payload);

        $signature = hash_hmac('sha256', $header_payload, $this->pass, true);

        return
            $this->base64url_encode($header) . '.' .
            $this->base64url_encode($payload) . '.' .
            $this->base64url_encode($signature);
    }

    /** Retorna o token conteúdo de um token JWT */
    function off(mixed $token): mixed
    {
        if (!is_stringable($token))
            return false;

        $token = explode('.', $token . '...');
        $header = $this->base64_decode_url($token[0]);
        $payload = $this->base64_decode_url($token[1]);

        $signature = $this->base64_decode_url($token[2]);

        $header_payload = $token[0] . '.' . $token[1];

        if (hash_hmac('sha256', $header_payload, $this->pass, true) !== $signature)
            return false;

        return json_decode($payload, true);
    }

    /** Verifica se uma variavel é um token JWT válido */
    function check(mixed $var)
    {
        if (is_string($var))
            return boolval($this->off($var));

        return false;
    }

    protected function base64url_encode($data)
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
    }

    protected function base64_decode_url($string)
    {
        return base64_decode(str_replace(['-', '_'], ['+', '/'], $string));
    }
}
