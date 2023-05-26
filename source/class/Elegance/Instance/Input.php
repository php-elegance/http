<?php

namespace Elegance\Instance;

use Elegance\Request;
use Exception;

class Input
{
    protected array $data = [];
    protected array $field = [];

    function __construct(?array $data = null)
    {
        $data = $data ?? Request::data();

        $this->data = array_map(fn ($v) => is_blank($v) ? null : $v, $data);
    }

    /** Retorna um objeto de um campo input */
    function &field(string $name, ?string $alias = null): InputField
    {
        if (!isset($this->field[$name])) {
            $this->field[$name] = new InputField(
                [$name, $alias],
                $this->data[$name] ?? null,
                array_key_exists($name, $this->data),
                $this
            );
        }
        return $this->field[$name];
    }

    /** Retorna o valor de um ou mais campos do input */
    function get(string|array $fields): mixed
    {
        $data = $this->data(...func_get_args());

        if (count($data) == 1) $data = array_shift($data);

        return $data;
    }

    /** Retorna os valores dos campos do input em forma de array */
    function data(null|string|array $fields = null): array
    {
        if (!is_array($fields) && func_num_args() > 1)
            $fields = func_get_args();

        $fields = $fields ?? array_keys($this->field);
        $fields = is_array($fields) ? $fields : [$fields];
        $fields = array_values($fields);

        foreach ($fields as $fieldName)
            $this->field($fieldName);

        $this->check();

        $data = [];
        foreach ($fields as $fieldName)
            $data[$fieldName] = $this->field($fieldName)->get();

        return $data;
    }

    /** Retorna os valores dos campos recebidos do input em forma de array */
    function dataRecived(null|string|array $fields = null): array
    {
        $data = $this->data(...func_get_args());

        foreach (array_keys($data) as $fieldName)
            if (!$this->field($fieldName)->recived())
                unset($data[$fieldName]);

        return $data;
    }

    /** Executa a checagem todos os campos do input lançando Exception em caso de erros */
    function check()
    {
        foreach ($this->field as &$feild)
            $feild->get();
    }

    /** Lança um erro de input */
    function send(string $message, mixed ...$parms): void
    {
        $send = [
            'message' => $message
        ];

        $status = STS_BAD_REQUEST;

        foreach ($parms as $param) {
            if (is_string($param))
                $send['description'] = $param;
            if (is_int($param))
                $status = $param;
        }

        throw new Exception(json_encode($send), $status);
    }
}
