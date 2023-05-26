<?php

namespace Elegance\Instance;

use Closure;
use Elegance\Prepare;
use Exception;

class InputField
{
    protected string $name;
    protected string $alias;
    protected mixed $value;
    protected bool $recived;

    protected bool $validated = false;
    protected array $validate = [];

    protected bool $sanitized = false;
    protected array $sanitize = [];
    protected mixed $valueSanitized = null;

    protected ?array $scapePrepare = [];

    protected bool $required = true;
    protected string $required_failMessage = 'required';
    protected array $required_failDescription = [];

    protected bool $useBlank = false;

    protected bool $preventTag = true;
    protected string $preventTag_failMessage = 'preventTag';
    protected array $preventTag_failDescription = [];

    function __construct(array|string $name, mixed $value, ?bool $recived = null)
    {
        $name = is_array($name) ? $name : [$name, $name];

        $this->name = array_shift($name);
        $this->alias = array_shift($name) ?? $this->name;
        $this->value = $value;
        $this->recived = $recived ?? is_blank($value);
    }

    /** Valida e retorna o valor do campo */
    function get(): mixed
    {
        $this->runValidate();
        return $this->runSanitize();
    }

    /** Se o campo foi recebido com um valor válido */
    function recived(): bool
    {
        if (!$this->recived)
            return false;

        if (is_blank($this->value))
            return $this->useBlank;

        return true;
    }

    /** Define se o campo deve usar valores vazios */
    function useBlank(bool $useBlank): static
    {
        $this->useBlank = $useBlank;
        return $this;
    }

    /** Define se o valor do input deve ser tratado com preventTag tags */
    function preventTag(bool $preventTag, ?string $message = null, ...$description): static
    {
        $this->preventTag = $preventTag;
        $this->preventTag_failMessage = $message ?? 'preventTag';
        $this->preventTag_failDescription = $description;

        return $this;
    }

    /** Define quais tags prepare o campo deve escapar */
    function scapePrepare(bool|array $scapePrepare = true): static
    {
        if (is_bool($scapePrepare))
            $scapePrepare = $scapePrepare ? [] : null;
        $this->scapePrepare = $scapePrepare;
        return $this;
    }

    /** Adiciona regras de validação do campo */
    function validate(mixed $rule, ?string $message = null, ...$description): static
    {
        $this->validated = false;

        if (is_bool($rule)) {
            $this->required = $rule;
            $this->required_failMessage = $message ?? 'required';
            $this->required_failDescription = $description;
        } else
        if (is_closure($rule)) {
            $this->validate[] = [$rule, $message, $description];
        } else
        if (match ($rule) {
            FILTER_VALIDATE_IP,
            FILTER_VALIDATE_MAC,
            FILTER_VALIDATE_URL,
            FILTER_VALIDATE_EMAIL,
            FILTER_VALIDATE_DOMAIN,
            FILTER_VALIDATE_REGEXP,
            FILTER_VALIDATE_BOOLEAN => true,
            default => false
        }) {
            $this->validate[] = [
                fn ($v) => filter_var($v, $rule),
                $message ?? $rule,
                $description
            ];
        } else
        if (is_class($rule, static::class)) {
            $this->validate[] = [
                fn ($v) => $v == $rule->value,
                $message ?? 'equal',
                $description,
                ['equal' => $rule->alias]
            ];
        } else
        if (is_closure($rule)) {
            $this->validate[] = [
                $rule,
                $message ?? 'default',
                $description
            ];
        } else
        if ($rule == FILTER_VALIDATE_INT) {
            $this->validate[] = [
                fn ($v) => intval($v) == $v,
                $message ?? $rule,
                $description
            ];
        } else
        if ($rule == FILTER_VALIDATE_FLOAT) {
            $this->validate[] = [
                fn ($v) => floatval($v) == $v,
                $message ?? $rule,
                $description
            ];
        }

        return $this;
    }

    /** Modo de sanitização do campo */
    function sanitize(Closure|int $sanitize): static
    {
        $this->sanitized = false;
        $this->sanitize[] = $sanitize;
        return $this;
    }

    /** Lança um erro de input com os dados do objeto */
    function send(string $message, mixed ...$parms): never
    {
        $send = [
            'field' => $this->name,
            'message' => $message,
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

    /** Rodas as regras de validação do campo */
    protected function runValidate()
    {
        if (!$this->validated) {
            $this->validated = true;
            $value = $this->value;

            if (!$this->recived()) {
                if ($this->required)
                    $this->validateError(
                        $this->required_failMessage,
                        $this->required_failDescription
                    );
                return;
            }

            if (is_string($value) && $this->preventTag && strip_tags($value) != $value)
                $this->validateError(
                    $this->preventTag_failMessage,
                    $this->preventTag_failDescription
                );

            foreach ($this->validate as $validate)
                if (!array_shift($validate)($value))
                    $this->validateError(...$validate);
        }
    }

    /** Executa um erro de validação */
    protected function validateError(string $message, ?array $description = [], array $prepare = []): never
    {
        $prepare['name'] = $this->alias;

        $message = InputMessage::get($message) ?? $message;
        $message = prepare($message, $prepare);

        $this->send($message, ...$description);
    }

    /** Roda as regras de sanitização do campo retornando o valor limpo */
    protected function runSanitize(): mixed
    {
        if (!$this->sanitized) {
            $value = $this->value;
            if ($this->recived()) {
                foreach ($this->sanitize as $sanitize) {
                    $sanitize = is_closure($sanitize) ? $sanitize : match ($sanitize) {
                        FILTER_SANITIZE_EMAIL => fn ($v) => strtolower(filter_var($v, FILTER_SANITIZE_EMAIL)),
                        FILTER_SANITIZE_NUMBER_FLOAT => fn ($v) => floatval(filter_var($v, FILTER_SANITIZE_NUMBER_FLOAT)),
                        FILTER_SANITIZE_NUMBER_INT => fn ($v) => intval(filter_var($v, FILTER_SANITIZE_NUMBER_INT)),
                        FILTER_SANITIZE_ENCODED,
                        FILTER_SANITIZE_ADD_SLASHES,
                        FILTER_SANITIZE_SPECIAL_CHARS,
                        FILTER_SANITIZE_FULL_SPECIAL_CHARS,
                        FILTER_SANITIZE_URL,
                        FILTER_UNSAFE_RAW => fn ($v) => filter_var($v, $sanitize),
                        default => fn ($v) => $v
                    };
                    $value = $sanitize($value);
                }
            }

            if (is_string($value) && is_array($this->scapePrepare))
                $value = Prepare::scape($value, $this->scapePrepare);

            $this->valueSanitized = $value;
            $this->sanitized = true;
        }

        return $this->valueSanitized;
    }
}
