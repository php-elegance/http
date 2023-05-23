<?php

namespace Elegance\ViewRender;

use Elegance\View;

abstract class ViewRender extends View
{
    /** Aplica ações extras ao renderizar uma view */
    abstract protected static function renderizeAction(string $content, array $params = []): string;
}
