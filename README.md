## Sobre.

Sistema de registro de ponto de funcionários pelo navegador.

-> Multi-Idioma (En, pt-Br): Testar alterando no .env
    ```
        APP_LOCALE=en
    ```
    or 
    ```
        APP_LOCALE=pt
    ```
-> O Sistema tem dois níveis de acesso, sendo eles:

1 - Administrador (Gestor)

    - Listagem de funcionários (Todos)
    - Remoção de funcionários (Somente os associados ao administrador)
    - Cadastro de funcionários
    - Alteração de funcionários (Somente os associados ao administrador)

    - Listagem de pontos de funcionarios (Todos)

2 - Funcionário (Subordinado do Gestor - Os funcionários estão associados ao Administrador que o cadastrou)

    - O funcionário pode registrar o seu ponto após autenticado no seu login
    - Visualizar seus pontos registrados


## Requisitos do ambiente de desenvolvimento

-   Docker

## Configurando seu ambiente

- Este projeto faz uso da ferramenta Laravel Sail.

- Após clonar o projeto, execute os seguintes comandos:

    - Entre na pasta do projeto:

    ```
    cd time-record
    ```

        Nota:
            - Em caso de reinstação remover pastas:

            ```
            rm -rf node_modules
            ```
            ```
            rm -rf vendor
            ```

    - Copiar o arquivo .env.exemple para .env

    ```
    cp .env.example .env
    ```

    - Rodar o comando:

    ```
    docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v $(pwd):/var/www/html \
    -w /var/www/html \
    laravelsail/php83-composer:latest \
    composer install --ignore-platform-reqs
    ```

    - Criar containers com sail:

    ```
    ./vendor/bin/sail up -d
    ```

    - Criar e popular tabelas do banco de dados:

    ```
    ./vendor/bin/sail artisan migrate:fresh --seed
    ```

    - Instalando dependências:

    ```
    ./vendor/bin/sail composer start
    ```

    - Isso deverá iniciar o projeto usando Docker (http://localhost).