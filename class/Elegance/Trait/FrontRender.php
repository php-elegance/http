<?php

namespace Elegance\Trait;

use Elegance\Code;
use Elegance\Request;
use Elegance\View;
use ScssPhp\ScssPhp\Compiler;
use ScssPhp\ScssPhp\OutputStyle;

trait FrontRender
{
    protected static ?string $base = null;
    protected static ?string $layout = null;

    /** Define a view que deve ser usada para compor a base do frontend */
    static function viewBase(?string $base)
    {
        self::$base = $base;
    }

    /** Define a view que deve ser usada para compor o layout do frontend */
    static function viewlayout(?string $layout)
    {
        self::$layout = $layout;
    }

    /** Retorna o conteúdo do front renderizado como array */
    protected static function renderToArray(string $content): array
    {
        $response = [
            'head' => self::getDinamicHead(),
            'render' => '__content',
            'hash' => null
        ];

        $layoutHash = Code::on(self::$layout ?? '@layout');
        $baseHash = Code::on(self::$base ?? '@base');

        $content = self::organizeHtml($content);

        if (Request::header('Front-Base-Hash') != $baseHash) {
            $response['hash'] = $baseHash;
            $response['render'] = '__base';
            $content = self::renderLayout($content);
            $content = self::renderBase($content);
        } else if (Request::header('Front-Layout-Hash') != $layoutHash) {
            $response['hash'] = $layoutHash;
            $response['render'] = '__layout';
            $content = self::renderLayout($content);
        }

        $content = str_replace_all(["\n\n", "\n ", "  "], ["\n", "\n", ' '], trim($content));

        $response['content'] = $content;

        return $response;
    }

    /** Retorna o conteúdo do front renderizado como HTML */
    protected static function renderToHtml(string $content): string
    {
        $content = self::organizeHtml($content);

        $content = self::renderLayout($content);
        $content = self::renderBase($content);
        $content = self::renderFront($content);

        $content = str_replace_all(["\n\n", "\n ", "  "], ["\n", "\n", ' '], trim($content));

        return $content;
    }

    /** Retorna o layout do front renderizado */
    protected static function renderLayout($content)
    {
        $content = "<div id='__content'>$content</div>";

        if (!is_null(self::$layout)) {
            $layout = View::render(self::$layout);

            $layout = self::organizeHtml($layout);

            $content = str_replace('[#content]', $content, $layout);
        }

        return $content;
    }

    /** Retorna a base do front renderizada */
    protected static function renderBase($content)
    {
        $hash = Code::on(self::$layout ?? '@layout');
        $content = "<div id='__layout' data-hash='$hash'>$content</div>";

        if (!is_null(self::$base)) {
            $base = View::render(self::$base);

            $base = self::organizeHtml($base);

            $content = str_replace('[#content]', $content, $base);
        }

        return $content;
    }

    /** Retorna o frontend renderizado */
    protected static function renderFront($content)
    {
        $hash = Code::on(self::$base ?? '@base');
        $content = "<div id='__base' data-hash='$hash'>$content</div>";

        $data = self::getDinamicHead();
        $data['content'] = '[#content]';

        $front = View::render(env('FRONT'), $data);

        preg_match_all('/<script[^>]*>(.*?)<\/script>/s', $front, $script);
        $script = implode("\n", $script[0] ?? []);
        $front = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $front);

        preg_match_all('/<style[^>]*>(.*?)<\/style>/s', $front, $style);
        $style = implode("\n", $style[1] ?? []);
        $front = preg_replace('#<style(.*?)>(.*?)</style>#is', '', $front);

        preg_match_all('/<head[^>]*>(.*?)<\/head>/s', $front, $head);
        $head = array_shift($head[0]);
        $front = str_replace($head, '[#head]', $front);
        $front = preg_replace('#<head(.*?)>(.*?)</head>#is', '', $front);

        preg_match_all('/<head[^>]*>(.*?)<\/head>/s', $head, $clsHead);
        $head = array_shift($clsHead[1]);

        $scssCompiler = new Compiler();
        $scssCompiler->setOutputStyle(OutputStyle::COMPRESSED);
        $style = $scssCompiler->compileString($style)->getCss();
        $style = !empty($style) ? "<style>$style</style>" : '';

        $head  = [
            $head,
            self::getTagsHead(),
            $style,
            $script
        ];

        $head = implode("\n", $head);
        $head = "<head>$head</head>";

        $front = prepare($front, [
            'head' => $head,
            'content' => $content,
        ]);

        return $front;
    }

    /** Retrona o HTML de um conteúdo organizado */
    protected static function organizeHtml($content)
    {
        preg_match_all('/<script[^>]*>(.*?)<\/script>/s', $content, $script);
        $script = implode("\n", $script[1] ?? []);
        $script = !empty($script) ? "<script>$script</script>" : '';

        $content = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $content);

        preg_match_all('/<style[^>]*>(.*?)<\/style>/s', $content, $style);
        $style = implode("\n", $style[1] ?? []);
        $scssCompiler = new Compiler();
        $scssCompiler->setOutputStyle(OutputStyle::COMPRESSED);
        $style = $scssCompiler->compileString($style)->getCss();
        $style = !empty($style) ? "<style>$style</style>" : '';

        $content = preg_replace('#<style(.*?)>(.*?)</style>#is', '', $content);

        return "$style\n$content\n$script";
    }
}
