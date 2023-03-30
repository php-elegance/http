<?php

namespace Elegance\Trait;

trait ViewCurrent
{
    protected static array $current = [];

    /** Inicializa uma view como atual */
    protected static function currentOpen(?string $ref, ?string $path,  ?string $name, string $type, array $data = [])
    {
        if (is_null($ref) || !count(self::$current) || self::current('ref') != $ref) {
            self::$current[] = [
                'ref' => $ref,
                'type' => $type,
                'path' => $path,
                'name' => $name,
                'in' => null,
                'data' => [...self::current('data'), ...$data]
            ];
            return true;
        }
        return false;
    }

    /** Finaliza a view atual */
    protected static function currentClose()
    {
        if (count(self::$current))
            array_pop(self::$current);
    }

    /** Retorna ou modifica as informações da view atual */
    static function current(string $info): null|string|array
    {
        if (count(self::$current)) {

            if (func_num_args() == 2) {
                $current = array_pop(self::$current);
                $current[$info] = func_get_arg(1);
                self::$current[] = $current;
            }

            $current = end(self::$current);
        }

        $current = $current ??  ['data' => []];

        return $current[$info] ?? null;
    }

    /** Manipula as tags preapre da view atual */
    static function currentData(array $data, int $merge = -1)
    {
        $merge = num_interval($merge, -1, 1);

        $currentData = self::current('data');

        self::current('data', match ($merge) {
            1 => [...$currentData, ...$data],
            0 => $data,
            -1 => [...$data, ...$currentData],
        });
    }

    /** Verifica se a view atual esta sendo renderiza em um tipo de view */
    static function currentIn(array $types): bool
    {
        if (count(self::$current) > 1) {
            $currentPrev = self::$current[count(self::$current) - 2]['type'];
            foreach ($types as $type)
                if (strtolower($type) == $currentPrev)
                    return true;
        }
        return false;
    }
}
