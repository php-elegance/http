<?php

namespace Elegance\Trait;

use Elegance\Code;
use Elegance\File;
use Elegance\Request;
use Elegance\View;
use ScssPhp\ScssPhp\Compiler;
use ScssPhp\ScssPhp\OutputStyle;

trait EnergizeRender
{
    protected static ?string $page = null;
    protected static ?string $layout = null;

    protected static function renderToArray($content)
    {
        $response = [
            'head' => self::getHead(),
            'render' => 'energize_content',
            'hash' => null,
            'content' => '',
        ];

        $layoutHash = Code::on(self::$layout ?? '@layout');
        $pageHash = Code::on(self::$page ?? '@page');

        $content = self::organizeHtml($content);

        if (Request::header('Energize-Page-Hash') != $pageHash) {
            $response['hash'] = $pageHash;
            $response['render'] = 'energize_page';
            $content = self::renderLayout($content);
            $content = self::renderPage($content);
        } else if (Request::header('Energize-Layout-Hash') != $layoutHash) {
            $response['hash'] = $layoutHash;
            $response['render'] = 'energize_layout';
            $content = self::renderLayout($content);
        }

        $content = str_replace_all(["\n\n", "\n ", "  "], ["\n", "\n", ' '], trim($content));

        $response['content'] = $content;

        return $response;
    }

    protected static function renderToHtml($content)
    {
        $content = self::organizeHtml($content);

        $content = self::renderLayout($content);
        $content = self::renderPage($content);
        $content = self::renderEnergize($content);

        $content = str_replace_all(["\n\n", "\n ", "  "], ["\n", "\n", ' '], trim($content));

        return $content;
    }

    /** Retorna o layout do front renderizado */
    protected static function renderLayout($content)
    {
        $content = "\n<div id='energize_content'>$content</div>\n";

        $layout = self::getView(self::$layout, 'layout/default.html');
        $layout = $layout ?? '=' . (dirname(__DIR__, 3) . "/view/base/layout/default.html");

        $layout = View::render($layout);
        $layout = self::organizeHtml($layout);
        $content = str_replace('[#content]', $content, $layout);

        return $content;
    }

    /** Retorna a página do front renderizada */
    protected static function renderPage($content)
    {
        $hash = Code::on(self::$layout ?? '@layout');
        $content = "\n<div id='energize_layout' data-hash='$hash'>$content</div>\n";

        $page = self::getView(self::$page, 'page/default.html');
        $page = $page ?? '=' . (dirname(__DIR__, 3) . "/view/base/page/default.html");

        $page = View::render($page);
        $page = self::organizeHtml($page);
        $content = str_replace('[#content]', $content, $page);

        return $content;
    }

    /** Retorna base do front rendereizada */
    protected static function renderEnergize($content)
    {
        $hash = Code::on(self::$page ?? '@page');
        $content = "\n<div id='energize_page' data-hash='$hash'>$content</div>\n";

        $data = [
            'head' => self::getHead(),
            'routeError' => url(env('ENERGIZE_ROUTE_ERROR'))
        ];

        $energize = self::getView(env('ENERGIZE_VIEW_BASE'), 'energize.html');
        $energize = $energize ?? '=' . (dirname(__DIR__, 3) . "/view/base/energize.html");

        $energize = View::render($energize, $data);

        preg_match_all('/<script[^>]*>(.*?)<\/script>/s', $energize, $script);
        $script = implode("\n", $script[0] ?? []);
        $energize = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $energize);

        preg_match_all('/<style[^>]*>(.*?)<\/style>/s', $energize, $style);
        $style = implode("\n", $style[1] ?? []);
        $energize = preg_replace('#<style(.*?)>(.*?)</style>#is', '', $energize);

        preg_match_all('/<head[^>]*>(.*?)<\/head>/s', $energize, $head);
        $head = array_shift($head[0]) ?? '';
        $energize = str_replace($head, '[#head]', $energize);
        $energize = preg_replace('#<head(.*?)>(.*?)</head>#is', '', $energize);

        preg_match_all('/<head[^>]*>(.*?)<\/head>/s', $head, $clsHead);
        $head = array_shift($clsHead[1]) ?? '';

        $scssCompiler = new Compiler();
        $scssCompiler->setOutputStyle(OutputStyle::COMPRESSED);
        $style = $scssCompiler->compileString($style)->getCss();
        $style = !empty($style) ? "<style>$style</style>" : '';

        $head  = [
            $head,
            $style,
            $script
        ];

        $head = implode("\n", $head);
        $head = "<head>\n$head\n</head>";

        $energize = prepare($energize, [
            'head' => $head,
            'content' => $content,
        ]);

        return $energize;
    }

    /** Retrona o HTML de um conteúdo organizado em style+content+script */
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


    /** Retorna uma referencia para uma view */
    protected static function getView(?string $view, string $defaultViewPath)
    {
        if (!is_null($view))
            return $view;

        if (File::check("view/base/$defaultViewPath"))
            return "=view/base/$defaultViewPath";
    }
}
