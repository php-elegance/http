# JWT

Cria e decodifica tokens JWT

> A classe Jwt utiliza a chave pass padrão. É extremamente recomendado altear esta chave para projetos em produção
> Para alterar a chave, defina a variavel **JWT** em suas variaveis de ambiente

### Utilizando a classe estatica

> A classe estatica sempre usa o pass definido nas variaveis de ambiente

    use Elegance\Jwt;

Retorna um token JWT com o conteúdo

    Jwt::on(mixed $payload): string

Retorna o token conteúdo de um token JWT

    Jwt::off(mixed $token): mixed

Verifica se uma variavel é um token JWT válido

    Jwt::check(mixed $var): bool

### Criando objeto de Jwt

Utilize instancias de Jwt para criar ou decodificar tokens que não utilizem a chave pass padrão.
Defina o pass que a instancia deve utilizar no parametro **$pass**

    $Jwt = new \Elegance\Instance\InstanceJwt($pass);

Retorna um token JWT com o conteúdo

    $Jwt->on(mixed $payload): string

Retorna o token conteúdo de um token JWT

    $Jwt->off(string $token): mixed

Verifica se uma variavel é um token JWT válido

     $Jwt->check(mixed $var): bool
