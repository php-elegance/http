# Request

Gerencia a requisição atual

    use Elegance/Request

---

**type**:  Retorna/Compara o tipo da requisição atual (GET, POST, PUT, DELETE, OPTIONS,) 

    Request::type(): string|bool

---

**header**: Retorna um ou todos os parametros header da requisição atual

    Request::header(): mixed

---

**ssl**: Retorna/Compara o status de utilização SSL da requisição atual

    Request::ssl(): bool

---

**host**: Retorna o host da requisiçaõ atual

    Request::host(): string

---

**path**: Retorna ou o todos os caminhos da URI da requisição atual

    Request::path(): array|string

---

**query**: Retorna ou o todos os parametros passados via query na requisição autal

    Request::query(): mixed

---

**route**: Retorna um ou todos os dados enviados via rota para a requisição atual

    Request::route(): mixed

---

**data**: Retorna um ou todos os dados enviados no corpo da requisição atual

    Request::data(): mixed

---

**file**: Retorna um o todos os arquivos enviados na requisição atual

    Request::file(): array

---

**set_header**: Define o valor de um parametro header da requisição atual

    set_header(string|int $name, mixed $value): void

---

**set_query**: Define o valor de um parametro query da requisição atual

    set_query(string|int $name, mixed $value): void

---

**set_data**: Define o valor de um parametro do corpo da requisição atual

    set_data(string|int $name, mixed $value): void

---

**set_route**: Define o valor de um parametro de rota da requisição atual

    set_route(string|int $name, mixed $value): void
