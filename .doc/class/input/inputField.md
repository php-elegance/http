# Input Field

Controla um campo especifico de input

    use \Elegance\Instance\InputField;

    $field = new InputField(array|string $name, mixed $value, ?bool $recived = null);

> E extremamente aconselhavel inicializar um InputFile usando o metodo **field** de um objeto [input](https://github.com/php-elegance/http/tree/main/.doc/class/input/input.md)

**Validação**

Para adicionar regras de validação ao field, utilize o metodo Validate

    $field->validate(mixed $rule, ?string $message = null, ...$description);

- O parametros $rule é a regra de validação.
- O campo Message é a mensagem em casao de falha
- O parametro Description pode conter uma descrição do erro, um status de retorno personalizado, ou ambos.

Pada definir um campo como obrigatório, utilize o metodo validate. Por padrão, todo campo criado é considerado obrigatório

    $field->validate(true);//Campo obrigatório

    $field->validate(false);//Campo opcional

> Caso o campo não for obrigatório, as regras de validação não vão ser aplicadas a menos que o campo seja informado

Em chamadas via API, o campo pode receber valores em branco que são desconsiderados. Para utilizar valores em branco no campo utilize o metodo useBlank

    $field->useBlank(bool $useBlank);

Pode-se definir filtros padrão do PHP como regras de validação. Cada um conta com a propria mensagem de erro padrão.

    $field->validate(FILTER_VALIDATE_EMAIL);
    $field->validate(FILTER_VALIDATE_URL);
    $field->validate(FILTER_VALIDATE_DOMAIN);

Caso precise verificar se um campo é igual a outro, informe o campo de comparação no metodo validate

    $feild1 = new InputField($name1,$value1);
    $feild2 = new InputField($name2,$value2);

    $field2->validate($field1);

Caso precis definir uma regra de validação personalizda, informe um objeto Closure no metodo validate

    $field->validate(function($v){
        return $v = 'validação';
    });

Multiplas validações podem ser adicionadas ao mesmo campo

    $field->validate(false)
        ->validate(FILTE_VALIDATE_EMAIL)
        ->validate(...);

**Validações automáticas**

**preventTag** Previne tags html no valor do campo (padrão TRUE)

    $field->preventTag(true);

**scapePrepare** Escapa tags de prepare no valor do campo (padrão TRUE)

    $field->scapePrepare(true);

**Sanitização**

Para adicinar uma forma de sanitizão do campo, utilize o metodo sanitaze

    $field->sanitaze($sanitaze);

Pode-se adicionar SANITIZE padrão do PHP

    $field->sanitaze(FILTER_SANITIZE_EMAIL);
    $field->sanitaze(FILTER_SANITIZE_NUMBER_INT);

Pode-se adicionar regras personalizadas de sanitaze

    $field->sanitaze(function($v){
        retrun strtolower($v);
    });

> As regras de sanitiação são aplicas **apos** as regras de validação

**Utilização**

**get**: Recupera o valor do campo, ou lança uma **Exception** em caso de erro

    $field->get();

**send**: Lança uma **Exception** em nome do campo

    $field->send($message,...$description);

**recived**: Verifica se o campo foi recebido

    $field->recived() :bool
