<?php

namespace Elegance;

use Elegance\ViewRender\ViewRenderScss;

abstract class Vue
{
    protected static array $vue = [];

    protected static array $script = [];
    protected static array $style = [];
    protected static array $incorp = [];
    protected static array $content = [];

    protected static array $renderized = [];

    /** Renderiza um componente ou aplicação Vuejs com base em uma referencia */
    static function render(string $vueRef, ?string $name, array $data = []): string
    {
        $content = '';
        $info = self::getInfoRef($vueRef);
        if ($info) {
            self::init($info);
            $content = Import::content(array_pop($info));
            $content = self::renderize($content, $name);
            self::close();
        }
        return $content;
    }

    /** Renderiza um componente ou aplicação VueJs com base em uma string */
    static function renderString(string $string, array $data = []): string
    {
        self::init([md5($string), null, null]);
        $content = self::renderize($string);
        self::close();
        return $content;
    }

    /** Renderiza um componente ou aplicação VueJs */
    protected static function renderize($content, ?string $name = null): string
    {
        $vue = end(self::$vue);

        $content = View::applyPrepare($content);

        list($template, $script, $style, $content) = self::explodeComponent($content);

        if (!isset(self::$renderized[$vue['key']])) {
            self::$renderized[$vue['key']] = true;

            $script = str_replace('export default', "let $vue[key] = ", $script);

            $template = trim($template);
            $template = str_replace_all(["\n", "  "], [' '], $template);
            if (!empty($template)) {
                $template = addslashes($template);
                $script .= "\n$vue[key].template = `$template`";
            }

            self::$style[] = $style;
            self::$script[] = $script;
            self::$script[] = "$vue[key].components = $vue[key].components ?? {};";
            self::$content[] = $content;
        }

        $content = '';

        if (count(self::$vue) === 1) {

            $divId = $name ?? $vue['key'];

            self::$incorp[] = "front.core.load.vue($vue[key],'#$divId')";

            $script = implode("\n", [...self::$script, ...self::$incorp]);
            if (!is_blank($script))
                $script = "<script>\n(function(){\n$script\n})()\n</script>";

            $style = implode("\n", self::$style);
            if (!empty($style)) {
                $style = ViewRenderScss::compileScss($style);
                if (!empty($style))
                    $style = "<style>\n$style\n</style>";
            }

            $content = implode("\n", self::$content);

            $content .= is_null($name) ? "$style\n<div id='$divId'></div>\n$script" : "$style\n$script";
            $content = str_replace_all(["\n\n", "\n ", "  "], ["\n", "\n", ' '], trim($content));
        } else {
            $name = $name ?? $vue['name'];
            $prev = self::$vue[count(self::$vue) - 2];
            self::$incorp[] = "$prev[key].components['$name'] = $vue[key];";
        }

        return $content ?? '';
    }

    /** Explode um arquivo vue em um array de [template,script,style,rest] */
    protected static function explodeComponent(string $content): array
    {
        $src = [];
        $script = [];
        preg_match_all('/<script[^>]*>(.*?)<\/script>/s', $content, $tag);
        $content = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $content);
        foreach ($tag[1] as $key => $value)
            if (empty(trim($value)))
                $src[] = $tag[0][$key];
            else
                $script[] = $value;

        $src = implode("\n", $src ?? []);
        $script = implode("\n", $script ?? []);

        preg_match_all('/<style[^>]*>(.*?)<\/style>/s', $content, $tag);
        $content = preg_replace('#<style(.*?)>(.*?)</style>#is', '', $content);
        $style = $tag[1];
        $style = implode("\n", $style ?? []);

        preg_match_all('/<template[^>]*>(.*?)<\/template>/s', $content, $tag);
        $content = preg_replace('#<template(.*?)>(.*?)</template>#is', '', $content);
        $template = implode("\n", $tag[1] ?? []);

        $content = trim($content);
        $content = "$src\n$content";

        return [$template, $script, $style, $content];
    }

    protected static function getInfoRef($vueRef)
    {
        if (str_starts_with($vueRef, '=')) {
            $vueRef = substr($vueRef, 1);
            $file = path($vueRef);

            if (!File::check($file))
                return false;

            return [
                md5("=$vueRef"), //KEY
                null, //PATH
                $file, //FILE
            ];
        }

        $file = path('vue', "$vueRef.vue");

        if (!File::check($file)) {
            $name = File::getOnly($vueRef);
            $name = explode('.', $name);
            $name = array_slice($name, 0, -1);
            $name = implode('.', $name);

            $vueRef = path(Dir::getOnly($vueRef), $name, File::getOnly($vueRef));
            $file = path('vue', $vueRef);

            if (!File::check($file))
                return false;
        }

        return [
            md5($vueRef), //KEY
            Dir::getOnly($vueRef), //PATH
            $file, //FILE
        ];
    }

    /** Inicia a renderização de um componente vue */
    protected static function init($info)
    {
        list($key, $path, $file) = $info;

        if (!is_null($file)) {
            $fileName = File::getOnly($file);
            $ex = File::getEx($fileName);
            $name = substr($fileName, 0, -1 * (strlen($ex) + 1));
            $name = strtoupper($name);
        }

        self::$vue[] = [
            'key' => "VUE_$key",
            'path' => $path,
            'name' => $name ?? null,
            'script' => [],
            'style' => [],
            'incorp' => []
        ];
    }

    /** Finaliza a renderizaçao de um componente vue */
    protected static function close()
    {
        array_pop(self::$vue);
        if (!count(self::$vue)) {
            self::$script = [];
            self::$style = [];
            self::$incorp = [];
            self::$renderized = [];
        }
    }
}
