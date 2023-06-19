# Router

Controla rotas do sistema

    use Elegance/Router

## Criando rotas manualmente

A classe conta um metodo para adiconar rotas manualmente

- **Router::add**: Adiciona uma rota para todas as requisições

  Router::add($template,$response);

> As ordem de declaração das rotas não importa pra a interpretação. A classe vai organizar as rotas da maneira mais segura possivel.

Para resolver as rotas, utilize o metodo **solve**

    Router::solve();

### Template

O template é a forma como a rota será encontrada na URL.

    Router::add('shop')// Reponde a URL /shop
    Router::add('blog')// Reponde a URL /blog
    Router::add('blog/post')// Reponde a URL /blog/post
    Router::add('')// Reponde a URL em branco

Para definir um parametro dinamico no template, utilize **[#]**

    Router::add('blog/[#]')// Reponde a URL /blog/[alguma coisa]
    Router::add('blog/post/[#]')// Reponde a URL /blog/post/[alguma coisa]

Caso a rota deva aceitar mais parametros alem do definido no template, utilize o sufixo **...**

    Router::add('blog...')// Reponde a URL /blog/[qualquer numero de parametros]

Para nomear os parametros dinamicos, pasta adicionar um nome ao **[#]**

    Router::add('blog/[#postId]')
    Router::add('blog/post/[#imageId]')

Para filtrar o tipo de parametro da URL, utilize os templates **[#]**, **[@]** e **[=]**

    Router::add('[#var]',...) // Qualquer valor
    Router::add('[@var]',...) // Valor numerico inteiro
    Router::add('[=var]',...) // O mesmo que a rota fixa var


 > Os parametros dinamicos podem ser recuperados utilizando a classe [Request](https://github.com/php-elegance/http/tree/main/.doc/class/request.md)

    Request::route(); //Retorna todos os parametros
    Request::route(0); //Retorna o primeiro parametro
    Request::route('var'); //Retorna o parametro de nome var

### Resposta

**callable**
Responda a rota com uma função anonima
A respota será o retorno da função anonima

    Router::add('', function (){
        return ...
    });

Pode recuperar um parametro dinamico informando-o como parametro para a função

    Router::add('blog/[#postId]', function ($postId){
        return ...
    });

---

### Middlewares

Para adicionar um middleware você deve utilizar o metodod **middleware**

    Router::middleware('route','middleware');

Pode-se definir que uma middleware não será executada em certas rotas prefixando as rotas com (!)

    Router::middleware('!route2','middleware');

Pode-se definir multiplas condições de rota para uma mesma middleware

    Router::middleware(['blog','!blog/all'],'middleware');

**veja**: [middleware](https://github.com/guaxinimdmx/elegance/tree/main/.doc/class/middleware.md)

> Uma rota personalizada para favicon.ico já é implementada. Isso evita um bug em navegadores que chamam este arquivo de forma automática. A rota pode ser subistituída a qualquer momento.

---

## File Route System (FRS)

A classe pode mapear um diretório adicionando rotas e middlewares automaticamente

    Router::map($dir);

Você pode mapear um diretório para dentro de um grupo de rotas adicionando um segundo parametro

    Router::map($dir, 'api');

Cada arquivo dentro do diretório se transforma em uma rota. Os arquivos devem retornar uma classe com os metodos HTTP que devem responeder

    <?php

        return new class
        {
            function get()
            {
                //...
            }

            function post()
            {
                //...
            }
        };

 > Uma classe de rota pode sofrer comportamentos inesperados se tiver o metodo \_\_construct.

Para criar rotas usando o FRS, crie um arquivo dentro do difertório mapeado. Arquivos com o nome \index.php serão chamados como padrão

    dir
        blog.php
        contact.php

    // Equivalente

    Router::add('blog');
    Router::add('contact');

Você pode criar subrotas separando o nome do arquivo com o caracter **+**

    dir
        blog.php
        blog+post.php

    // Equivalente

    Router::add('blog');
    Router::add('blog/post');

Para organização, pode-se criar subrotas utilizando diretórios

    dir
        blog
            post.php
        blog.php

    // Equivalente

    Router::add('blog');
    Router::add('blog/post');

Um arquivo **\index.php** será chamado automaticamente como rota principal

    dir
        index.php

    // Equivalente

    Router::add('');

Pode adicionar o arquivo **\index.php** dentro de um diretório

    dir
        blog
            index.php

    // Equivalente

    dir
        blog.php

    // Equivalente

    Router::add('blog');

### Template

par se rotas dinamicas, adicione os templates ao nome dos arquivos

    dir
        [#var].php
        [@var].php
        [=var].php
        =var.php

    // Equivalente
    Router::add('[#var]') // Qualquer valor
    Router::add('[@var]') // Valor numerico inteiro
    Router::add('[=var]') // O mesmo que a rota fixa var
    Router::add('var.php') // A rota deve ter a extensão do arquivo

O sufixo **...** deve ser usado como um template **[...]**

    dir
        [...].php

    // Equivalente

    Route::add('...');

### Middleware

Para adicionar middlewares, basta adicionar um arquivo **\_.php** no diretório

    dir
        _.php

O arquivo deve retornar uma lista de middlewares e será chamado quando a rota for ativada. Todos os arquivos **\_.php**, que foram compativeis com a rota, serão chamados e acumulados.

Um exemplo de um arquivo de middleware **\_.php**

    <?php

        return [
            'md1',
            'md2',
            function($next){
                ...
                return $next();
            }
        ];

### Tipos de arquivo de rota
Os arquivos podem ser do tipo PHP, HTML, CSS, SCSS e JS. A classe tentará respeitar o tipo de resposta de cada aquivo

 - **php, hml**: resposta html
 - **css, scss**: CSS
 - **js** => js

Arquivos de rota que não retornarem nada, ou retornar uma string, serão tratados como uma [view](https://github.com/guaxinimdmx/elegance/tree/main/.doc/class/view.md).