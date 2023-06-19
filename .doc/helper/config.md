# Config

**CACHE**: Tempo de cache para arquivos em horas

    CACHE = null //Não altera o comportamento de cache
    CACHE = true //Não altera o comportamento de cache

    CACHE = false //Bloqueia cache
    CACHE = 0 //Bloqueia cache

    CACHE = 24 //Utiliza um cache de 24 horas

**CACHE_EXEMPLE**: Tempo de cache para arquivos de uma extensão [.exemple] em horas

    CACHE_JPG = 672 //Cache para arquivo .jpg
    CACHE_ICO = 672 //Cache para arquivo .ico
    CACHE_ZIP = 24 //Cache para arquivo .zip
    CACHE_PDF = 12 //Cache para arquivo .pdf
    CACHE_...

**CROS**: Utilizar a solução embitida de CROS

    CROS = true

**JWT**: Chave para criação e verificação de tokens JWT

    JWT = eleganceJwtPass

**PORT**: Porta para a utilização do servidor embutido

    PORT = 8333