<?php

namespace Elegance;

use Elegance\Trait\ViewCurrent;
use Elegance\Trait\ViewPrepare;
use Elegance\Trait\ViewSuportedTypes;

abstract class View
{
    use ViewCurrent;
    use ViewPrepare;
    use ViewSuportedTypes;

    static function render(string $viewRef, array $data = [])
    {
        if (str_starts_with($viewRef, '>>')) {
            $viewRef =  substr($viewRef, 2);
            self::currentSet_encpas($viewRef);
        } else {

            $info = self::getInfoRef($viewRef);

            if ($info) {
                list($key, $path, $file, $type) = $info;

                if (self::currentOpen($key, $path, $file, $type, $data)) {

                    if ($type == 'php') {
                        list($content, $type, $data) = (function ($__FILEPATH__, $__PARAMS__) {
                            foreach (array_keys($__PARAMS__) as $__KEY__)
                                if (!is_numeric($__KEY__))
                                    $$__KEY__ = $__PARAMS__[$__KEY__];

                            $__DATA = $__PARAMS__;
                            $__TYPE = 'html';

                            ob_start();
                            require $__FILEPATH__;
                            $__OUTPUT__ = ob_get_clean();

                            return [$__OUTPUT__, $__TYPE, $__DATA];
                        })($file, self::currentGet_data());

                        foreach ($data as $var => $value)
                            self::currentSet_data($var, $value);

                        self::currentSet_type('type', $type);
                    } else {
                        $content = Import::output($file, self::currentGet_data());
                    }

                    $content = self::renderize($content);
                    self::currentClose();
                }
            }
        }

        return $content ?? '';
    }

    /** Renderiza uma string como uma view html */
    static function renderString(string $string, array $data = [], string $type = 'html'): string
    {
        if (!self::checkSuportedType($type))
            return '';

        self::currentOpen(null, null, null, $type, $data);
        $content = self::renderize($string);
        self::currentClose();

        return $content;
    }

    protected static function renderize(string $content, array $params = []): string
    {
        $renderClass = '\\Elegance\\ViewRender\\ViewRender' . ucfirst(self::currentGet_type());

        if (class_exists($renderClass) && is_extend($renderClass, ViewRender::class))
            $content = $renderClass::renderizeAction($content, $params);
        else
            $content = self::applyPrepare($content);

        if (self::currentGet_encpas())
            $content = self::render(self::currentGet_encpas(), ['content' => $content]);

        return $content;
    }

    protected static function getInfoRef($viewRef)
    {
        $type = File::getEx($viewRef);

        if (!self::checkSuportedType($type))
            return false;

        if (str_starts_with($viewRef, '='))
            return [
                md5($viewRef), //KEY
                null, //PATH
                substr($viewRef, 1), //FILE
                self::getSuportedType($type), //TYPE
            ];

        $path = str_starts_with($viewRef, '@') ? substr($viewRef, 1) : path(self::currentGet_path(), $viewRef);
        $file = path('view', $path);

        if (!File::check($file)) {
            $name = File::getOnly($path);
            $name = explode('.', $name);
            $name = array_slice($name, 0, -1);
            $name = implode('.', $name);

            $path = path(Dir::getOnly($path), $name, File::getOnly($path));
            $file = path('view', $path);

            if (!File::check($file))
                return false;
        }

        return [
            md5($viewRef), //KEY
            Dir::getOnly($path), //PATH
            $file, //FILE
            self::getSuportedType($type), //TYPE
        ];
    }
}
