# Response

Classe de resposta para a requisição atual

    use \Elegance\Response;

**status**: Define o status HTTP da resposta

    Response::status(?int $status): void

**header**: Define um cabeçalho para a resposta

    Response::header(string|array $name, ?string $value = null)

**type**: Define o contentType da resposta

    Response::type(?string $type, bool $replace = true)

**content**: Define o conteúdo da resposta

    Response::content(mixed $content, bool $replace = true)

**cache**: Define se o arquivo deve ser armazenado em cache

    Response::cache(null|bool|int $time): void

**download**: Define se o navegador deve fazer download da resposta

    Response::download(null|bool|string $download): void

**send**: Envia a resposta ao navegador do cliente

    Response::send(): never

**getStatus**: Retorna o status atual da resposta

    Response::getStatus(): ?int

**getContent**: Retorna o conteúdo atual da resposta

    Response::getContent(): ?string

**checkType**: Verifica se o tipo da resposta é um dos tipos informados

    Response::checkType(): bool