<?php

namespace Elegance\Trait;

use Elegance\Code;

trait ViewVue
{
    protected static array $vue = [];

    protected static array $vueScript = [];
    protected static array $vueStyle = [];
    protected static array $vueIncorp = [];

    protected static array $vueRenderized = [];

    protected static function renderVue($content, $name = null)
    {
        self::vueInit($name);

        $content = self::applyPrepare($content);

        $vue = end(self::$vue);

        if (!isset(self::$vueRenderized[$vue['ref']])) {
            self::$vueRenderized[$vue['ref']] = true;

            list($script, $style) = self::explodeVue($content, $vue['ref']);

            self::$vueScript[] = $script;
            self::$vueStyle[] = $style;

            self::$vueScript[] = prepare('[#ref].components = [#ref].components ?? {};', $vue);
        }

        if ($vue['isApp']) {
            self::$vueIncorp[] = prepare("front.load.vue([#ref],'[#name]')", $vue);

            $script = [
                implode("\n", self::$vueScript),
                implode("\n", self::$vueIncorp),
            ];

            $style = self::$vueStyle;

            $style = implode("\n", $style);
            $script = implode("\n", $script);

            $style = is_blank($style) ? '' : "\n<style>\n$style\n</style>\n";
            $script = is_blank($script) ? '' : "\n<script>\n$script\n</script>\n";

            if ($vue['isFull']) {
                return "$style<div id='$vue[name]'></div>$script";
            } else {
                return "$style$script";
            }
        } else {
            self::$vueIncorp[] = prepare('[#prev.ref].components["[#vue.name]"] = [#vue.ref];', [
                'prev' => self::$vue[count(self::$vue) - 2],
                'vue' => $vue
            ]);
        }

        self::vueClose();

        return '';
    }

    /** Explode uma string de component vue em [template,script,stype] */
    protected static function explodeVue($content, $ref)
    {
        preg_match_all('/<template[^>]*>(.*?)<\/template>/s', $content, $template);
        $template = implode("\n", $template[1] ?? []);

        preg_match_all('/<script[^>]*>(.*?)<\/script>/s', $content, $script);
        $script = implode("\n", $script[1] ?? []);
        $script = is_blank($script) ? 'export default {};' : $script;

        preg_match_all('/<style[^>]*>(.*?)<\/style>/s', $content, $style);
        $style = implode("\n", $style[1] ?? []);

        $script = str_replace('export default', "let $ref = ", $script);

        $template = trim($template);
        $template = str_replace_all(["\n", "  "], [' '], $template);
        if (!empty($template)) {
            $template = addslashes($template);
            $script .= "\n$ref.template = `$template`";
        }

        return [$script, $style];
    }

    /** Inicia a renderização de um componente vue */
    protected static function vueInit($name)
    {
        $isApp = !count(self::$vue);
        $isFull = $isApp && is_null($name);

        self::$vue[] = [
            'isApp' => $isApp,
            'isFull' => $isFull,
            'ref' => Code::on(self::current('ref')),
            'name' => $name ?? self::current('name'),
            'script' => [],
            'style' => [],
            'incorp' => [],
        ];
    }

    /** Finaliza a renderizaçao de um componente vue */
    protected static function vueClose()
    {
        array_pop(self::$vue);

        if (!count(self::$vue)) {
            self::$vueScript = [];
            self::$vueStyle = [];
            self::$vueIncorp = [];
            self::$vueRenderized = [];
        }
    }

    /** Retorna o conteúdo HTML de um DOMElement */
    protected static function vueInnerHTML(\DOMElement $element)
    {
        $doc = $element->ownerDocument;
        $html = '';
        foreach ($element->childNodes as $node)
            $html .= $doc->saveHTML($node);
        return $html;
    }
}
