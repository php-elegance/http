# Input Error

Armazena as mensagens de erro padrão para input

    use \Elegance\Instance\inputMessage;

**Adicionar**

Para adicionar ou editar uma menssagem de erro, utilize o metodo **set**

    inputMessage::set($type,$message);

**Retornar**

Para retornar uma menssagem de erro, utilize o metodo **get**

    inputMessage::get($type);

**Padrão**
A classe conta com 13 menssagens padrão que são usadas nos erros do [InputField](https://github.com/php-elegance/http/tree/main/.doc/class/input/inputField.md). Sinta-se livre para altera-las.

**FILTER_VALIDATE_IP**
O campo [#name] precisa ser um endereço IP

**FILTER_VALIDATE_INT**
O campo [#name] precisa ser um numero inteiro

**FILTER_VALIDATE_MAC**
O campo [#name] precisa ser um endereço MAC

**FILTER_VALIDATE_URL**
O campo [#name] precisa ser uma URL

**FILTER_VALIDATE_EMAIL**
O campo [#name] precisa ser um email

**FILTER_VALIDATE_FLOAT**
O campo [#name] precisa ser um numero

**FILTER_VALIDATE_DOMAIN**
O campo [#name] precisa ser um dominio

**FILTER_VALIDATE_REGEXP**
O campo [#name] precisa ser um a expressão regular

**FILTER_VALIDATE_BOOLEAN**
O campo [#name] precisa ser um valor booleano

**required**
O campo [#name] é obrigatório

**preventTag**
O campo [#name] contem um valor inválido

**default**
O campo [#name] contem um erro

**equal**
O campo [#name] deve ser igual o campo [#equal]
