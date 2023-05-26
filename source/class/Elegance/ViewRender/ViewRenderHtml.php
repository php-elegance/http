<?php

namespace Elegance\ViewRender;

abstract class ViewRenderHtml extends ViewRender
{
    /** Aplica ações extras ao renderizar uma view */
    protected static function renderizeAction(string $content, array $params = []): string
    {
        $content = self::applyPrepare($content);

        if (count(self::$current) == 1)
            $content = self::organizeHtml($content);

        return $content;
    }

    /** Retorna uma string HTML organizando as tags style e script */
    static function organizeHtml(string $string): string
    {
        preg_match('/<html[^>]*>(.*?)<\/html>/s', $string, $html);

        $string = count($html) ? self::organizeComplete($string) : self::organizePartial($string);

        $string = str_replace_all(["\n\n", "\n ", "  "], ["\n", "\n", ' '], trim($string));

        return $string;
    }

    /** Aplica a organização em uma estrutura HTML parcial */
    protected static function organizePartial(string $string): string
    {
        $src = [];
        $script = [];
        preg_match_all('/<script[^>]*>(.*?)<\/script>/s', $string, $tag);
        $string = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $string);
        foreach ($tag[1] as $key => $value)
            if (empty(trim($value)))
                $src[] = $tag[0][$key];
            else
                $script[] = $value;

        $src = implode("\n", $src ?? []);
        $script = implode("\n", $script ?? []);

        if (!empty($script))
            $script = "<script>$script</script>";

        preg_match_all('/<style[^>]*>(.*?)<\/style>/s', $string, $tag);
        $string = preg_replace('#<style(.*?)>(.*?)</style>#is', '', $string);
        $style = $tag[1];

        $style = implode("\n", $style ?? []);

        if (!empty($style)) {
            $style = ViewRenderScss::compileScss($style);
            if (!empty($style))
                $style = "<style>$style</style>";
        }

        $string = [$src, $style, $string, $script];
        $string = implode("\n", $string);

        return $string;
    }

    /** Aplica a organização em uma estrutura HTML completa*/
    protected static function organizeComplete(string $string): string
    {
        $src = [];
        $script = [];
        preg_match_all('/<script[^>]*>(.*?)<\/script>/s', $string, $tag);
        $string = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $string);
        foreach ($tag[1] as $key => $value)
            if (empty(trim($value)))
                $src[] = $tag[0][$key];
            else
                $script[] = $value;

        $src = implode("\n", $src ?? []);
        $script = implode("\n", $script ?? []);

        if (!empty($script))
            $script = "<script>\n$script\n</script>";

        preg_match_all('/<style[^>]*>(.*?)<\/style>/s', $string, $tag);
        $string = preg_replace('#<style(.*?)>(.*?)</style>#is', '', $string);
        $style = $tag[1];

        $style = implode("\n", $style ?? []);

        if (!empty($style)) {
            $style = ViewRenderScss::compileScss($style);
            if (!empty($style))
                $style = "<style>\n$style\n</style>";
        }

        preg_match_all('/<head[^>]*>(.*?)<\/head>/s', $string, $tag);
        $string = str_replace($tag[0], '[#head]', $string);
        $string = preg_replace('#<head(.*?)>(.*?)</head>#is', '', $string);
        $head = $tag[1];

        $head[] = $style;
        $head[] = $src;
        $head[] = $script;

        $head = implode("\n", $head);
        $head = "<head>\n$head\n</head>";

        $string = prepare($string, ['head' => $head]);

        return $string;
    }
}
