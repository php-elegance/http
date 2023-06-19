# View

Camada de visualização para aplicações Elegance

### Diretório

Uma **view**, é um diretório que contem os arquivos para montar a camada de visualiação, deve estar localizada na raiz do seu projeto e ser acessivel via **/view**.

### Utilização

Ao chamar uma view é o mesmo que executar um [prepare](https://github.com/php-elegance/core/blob/main/.doc/class/prepare.md) em seus arquivos. A classe se encarrega de montar a view da melhor forma possivel. Podem ser chamados via metodo, helper.

    View::render($ref,$prepare):string;
    view($ref,$prepare):string;

Você pode chamar views dentro de um subdiretório, para isso, adicione o caminho relativo para o arquivo partindo do diretório view/

    view('index.html') // Chama o arquivo view/index.html
    view('nav/top.html') // Chama o arquivo view/nav/top.html

### Escrevendo view

Uma view pode ser um arquivo HTML, JS, CSS, SCSS PHP e VUE. Organize os arquivos em subdiretórios da forma que for mais confortável. Escreva o arquivo de view normalmente

    <h1>Isso é uma view</h1>

Ao chamar uma view você pode fornecer um array de prepare. Todas a variaveis do array vão estar disponiveis no arquivo de view

    //view/index.html
    <h1>[#name]</h1>

    //Chamada da view
    view('index.html',['name'=>'Pedro']);

    //Saída
    <h1>Pedro</h1>

Você pode utilizar a tag prepare **[#view]** para realizar chamadas dentro dos arquivos. Usar a tag prepare junto com o prefixo **@** vai importar um aquivo de view utilizando o prepare da chamada original e partindo do diretório da view.

    //view/default/content.html
    <b>Ola [#name]</b>
    <p>...</p>

    //view/default/index.html
    <h1>[#name]</h1>
    [#view:@content.html]

    //Chamada da view
    view('default/index.html',['name'=>'Pedro']);

    //Saída
    <h1>Pedro</h1>
    <b>Ola Pedro</b>
    <p>...</p>

Quando importamos uma view sem o prefixo **@** via perpare, a classe inicia a busca do diretório padrão.

    //view/default/index.html
    <h1>[#name]</h1>
    [#view:content.html] //  Chama a view /view/content.html
    [#view:@content.html] // Chama a view /view/default/content.html

Se precisar chamar um arquivo fora do diretório princial de view, utilize o prefixo **=**. Neste caso, deve-se passar o caminho completo para o arquivo

    view('=library/assets/style.css');
    ou
    [#view:=library/assets/style.css]

Mesmo que o prepare seja executado antes do arquivo, alguns editores podem reconhecer a tag prepare como um erro. Para evitar a sinalizalção de erro do editor pode-se colocar a tag prepare atras de um comentário.
Todas as linhas abaixo vão produzir o mesmo resultado

    [#view:...]
    <!-- [#view:...] -->
    <!--[#view:...]-->
    //[#view:...]
    /* [#view:...] */
    /*[#view:...]*/

Pode-se misturar tipos de arquivo com as chamadas via prepare. Lembre-se de encapsular corretamente o conteúdo dos aquivos.

    //view/style.css
    h1{ color: red; }

    //view/index.html
    <h1>Ola mundo</h1>
    <style>[#view:style.css]</style>

    //Saída
    <h1>Ola mundo</h1>
    <style>h1{ color: red; }</style>

Você pode utilizar a chamada **[#view]** para encapsular o conteúdo do arquivo dentro de outro aquivo. Isso é extremamente util para criar templates reutilizaveis. Par isso, adicione o prefixo **[>>]**

> A view que receberá o conteúdo deve ter a tag prepare **[#content]**

    //view/card.html
    <div>
        <h2>Card para [#name]</h2>
        [#content]
    </div>

    //view/index.html
    <span>Lorem ipsum dolor ...</span>
    [#view:>>content.html]

    //Chamada da view
    view('index.html',['name'=>'Pedro']);

    //Saída
    <div>
        <h2>Card para Pedro</h2>
        <span>Lorem ipsum dolor ...</span>
    </div>

### Comportamento de view

Em geral, todas as views tem o mesmo comportamento. Todas tem seu conteúdo importado e recebem o tratamento via prepare.
Algumas views, no entanto, tem algum comportamento extra.

- **view.css**: O CSS é minificado antes do retorno
- **view.html**: Recebe o tratamento padrão
- **view.js**: Recebe o tratamento padrão
- **view.php**: O conteúdo é interpretado antes da importação e tratado como um tipo dinamico.
- **view.scss**: O SCSS é compilado e minificado antes do retorno

### Considerações view CSS e SCSS

As views CSS e SCSS terão sem conteúdo compilado e minificado antes do retono. Como seu conteúdo é importado via PHP, a chamada **include** do scss não deve ser utilizada. Ao invez disso, utilize a chamada de views para obter o mesmo resultado.

    import './newFile.scss'// Vai gerar um Erro 500
    [#view:newFile.scss]// Obtem o resultado do import
    import url(...)// Pode ser usado normalmente

### Considerações view js

Views do topo JS são importadas e recebem o tratamento do prepare. No entando é importate lembrar que o preapre trata tido como **STRING**, então lembre-se de tratar os campos caso precise do valor em tipo diferente.

    let varName = '[#name]'
    let varInt = intVal('[#int]');
    let varJson = JSON.parse('[#json]');

> Embora não seja recomendado, você pode importar os campos sem o tratamento, isso vai funcionar mas seu editor de testo provavelmente vai reconhecer um erro no arquivo.

