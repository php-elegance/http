<?php

use Elegance\View;

if (!function_exists('view')) {

    /** Renderiza uma view baseando em uma referencia de arquivo */
    function view(string $ref, array $data = [], ...$params): string
    {
        return View::render($ref, $data, ...$params);
    }
}

if (!function_exists('viewType')) {

    /** Altera o tipo da view ativa */
    function viewType(string $type): void
    {
        View::current('type', strtolower($type));
    }
}

if (!function_exists('viewData')) {

    /** Adiciona ou altera uma tag prepare da view atual */
    function viewData(string $var, mixed $value, bool $replace = false): void
    {
        $data = View::current('data');
        if ($replace || !isset($data[$var])) {
            $data[$var] = $value;
            View::current('data', $data);
        }
    }
}
