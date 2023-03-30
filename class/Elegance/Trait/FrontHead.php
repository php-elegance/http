<?php

namespace Elegance\Trait;

use Elegance\View;

trait FrontHead
{
    protected static ?string $title = null;
    protected static ?string $favicon = null;
    protected static ?string $description = null;

    protected static string $routeError = 'error';

    protected static array $tagStyle = [];
    protected static array $tagScript = [];

    /** Adiciona uma view ou arquivo para ser adicionado ao front via tag script */
    static function tagScript(string $scriptRef)
    {
        self::$tagScript[] = $scriptRef;
    }

    /** Adiciona uma view ou arquivo para ser adicionado ao front via tag style */
    static function tagStyle(string $styleRef)
    {
        self::$tagStyle[] = $styleRef;
    }

    /** Define o titulo que deve ser usado no front */
    static function dinamicTitle(?string $title)
    {
        self::$title = $title;
    }

    /** Define a descrição que deve ser usado no front */
    static function dinamicDescription(?string $description)
    {
        self::$description = $description;
    }

    /** Define o icone favicon que deve ser usado no front */
    static function dinamicFavicon(?string $favicon)
    {
        self::$favicon = $favicon;
    }

    /** Define a rota que deve ser utilizada como página de erro */
    static function routeError(string $route)
    {
        self::$routeError = $route;
    }

    /** Retorna os dados para composição do head do frontend */
    protected static function getDinamicHead()
    {
        $head = [];

        $head['title'] = self::$title ?? 'Elegance';
        $head['description'] = self::$description ?? '';
        $head['favicon'] = self::$favicon ?? url('favicon.ico');

        return $head;
    }

    /** Retorna as tags de script e style que devem ser adicionadas ao head do front */
    protected static function getTagsHead()
    {
        $tagHeads = [];

        foreach (self::$tagStyle as $style) {
            if (str_starts_with($style, 'http://') || str_starts_with($style, 'https://')) {
                $tagHeads[] = "<link rel='stylesheet' href='$style'>";
            } else {
                $style = str_starts_with($style, '@') ? View::render($style) : $style;
                $tagHeads[] = !empty($style) ? "<style>$style</style>" : '';
            }
        }

        $tagHeads[] = prepare("<script>const fError = '[#]'</script>", url(self::$routeError));

        $tagHeads[] = prepare("<script src='[#]'></script>", url('front.js'));

        foreach (self::$tagScript as $script) {
            if (str_starts_with($script, 'http://') || str_starts_with($script, 'https://')) {
                $tagHeads[] = "<script src='$script'></script>";
            } else {
                $script = str_starts_with($script, '@') ? View::render($script) : $script;
                $tagHeads[] = !empty($script) ? "<script>$script</script>" : '';
            }
        }

        return implode("\n", $tagHeads);
    }
}
