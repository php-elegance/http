<?php

namespace Elegance\Trait;

trait ViewCurrent
{
    protected static array $current = [];

    /** Inicializa uma view como atual */
    static function currentOpen($key, $path, $file, $type, array $data): bool
    {
        if (is_null($key) || $key != self::currentGet_key() && !isset(self::$current[$key])) {
            $currentData = self::currentGet_data() ?? [];
            self::$current[$key] = [
                'key' => $key,
                'path' => $path,
                'file' => $file,
                'encaps' => null,
                'data' => [...$currentData, ...$data],
                'type' => $type
            ];
            return true;
        }
        return false;
    }

    /** Finaliza a view atual */
    static function currentClose(): void
    {
        if (count(self::$current)) array_pop(self::$current);
    }

    /** Adiciona uma view de encapsulamento para view atual */
    static function currentSet_encpas(string $viewRef): void
    {
        if (count(self::$current)) {
            $current = array_pop(self::$current);
            $current['encaps'] = $viewRef;
            self::$current[] = $current;
        }
    }

    /** Adiciona dados ao prepare da view atual */
    static function currentSet_data(string $var, mixed $value): void
    {
        if (count(self::$current)) {
            $current = array_pop(self::$current);
            $current['data'][$var] = $value;
            self::$current[] = $current;
        }
    }

    /** Define o tipo da view atual */
    static function currentSet_type(string $type): void
    {
        if (count(self::$current) && self::checkSuportedType($type)) {
            $current = array_pop(self::$current);
            $current['type'] = self::getSuportedType($type);
            self::$current[] = $current;
        }
    }

    /** Retorna a chave de identificação da view atual */
    static function currentGet_key(): ?string
    {
        if (count(self::$current))
            return end(self::$current)['key'];

        return null;
    }

    /** Retorna o caminho do diretório da view atual */
    static function currentGet_path(): ?string
    {
        if (count(self::$current))
            return end(self::$current)['path'];

        return null;
    }

    /** Retorna o nome do arquivo da view atual */
    static function currentGet_file(): ?string
    {
        if (count(self::$current))
            return end(self::$current)['file'];

        return null;
    }

    /** Retorna o tipo do arquivo de view atual */
    static function currentGet_type(): ?string
    {
        if (count(self::$current))
            return end(self::$current)['type'];

        return null;
    }

    /** Retorna o prepare para ser utilizado na view atual */
    static function currentGet_data(): array
    {
        if (count(self::$current))
            return end(self::$current)['data'];

        return [];
    }

    /** Adiciona uma view de encapsulamento para view atual */
    static function currentGet_encpas(): ?string
    {
        if (count(self::$current))
            return end(self::$current)['encaps'];

        return null;
    }
}
