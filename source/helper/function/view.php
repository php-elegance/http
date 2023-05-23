<?php

use Elegance\View;

if (!function_exists('view')) {

    /** Renderiza uma view baseando em uma referencia de arquivo */
    function view(string $ref, array $data = []): string
    {
        return View::render($ref, $data);
    }
}
