<?php

namespace Elegance\ViewRender;

abstract class ViewRenderJs extends ViewRender
{
    /** Aplica ações extras ao renderizar uma view */
    protected static function renderizeAction(string $content, array $params = []): string
    {
        $content = self::applyPrepare($content);

        if (count(self::$current) == 1)
            $content = self::removeTrash($content);

        return $content;
    }

    /** Retorna uma string Javascript removendo espaçoas e quebras de linha duplicadas */
    static function removeTrash(string $string): string
    {
        return str_replace_all(["\n\n", "\n ", "  "], ["\n", "\n", ' '], trim($string));
    }
}
