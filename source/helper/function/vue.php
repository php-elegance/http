<?php

use Elegance\Vue;

if (!function_exists('vue')) {

    /** Renderiza um componente vuejs */
    function vue(string $ref, ?string $name = null, array $data = []): string
    {
        return Vue::render($ref, $name, $data);
    }
}
