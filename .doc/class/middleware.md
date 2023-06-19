# Middleware

Ações executadas antes da resposta da requisição

    use Elegance/Middleware

### Estrutura

As middlewares são funções que recebem um valor, realizam uma ação e chamam a proxima. 
O template basico de uma middleware é o seguinte

    function (Closure $next){
        return $next();
    }

Caso a middlewares seja uma classe, deve ser implementado o metodo **__invoke**

    function __invoke(Closure $next){
        return $next();
    }

### Criando middlewares

    php mx create.middleware [nomeDaMiddleware]

Isso vai criair um arquivo dentro do namespace **Middleware** com o nome fornecido

### Manipulando fila de middlewares
Para adicionar uma middleware na fila de execução, utilize o codigo abaixo

    Middleware::queue('middlewareName');

### Executando middlewares
Para executar middlewares, utilize o metodo estatico **run**

    Middleware::run($action);

Para executar um conjunto de middleware especifica, informe o array no metodo **run**

    Middleware::run($middlewares, $action);
