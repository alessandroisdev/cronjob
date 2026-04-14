<p align="center">
  <h1 align="center">⚙️ QueueWorker Engine</h1>
  <p align="center">Processamento de filas assíncronas inteligente e tolerante a falhas feito em PHP 8.4.</p>
</p>

## 📖 Sobre o Projeto
O **QueueWorker Engine** (CronJob) é um motor de mensageria baseada em banco de dados (_Database-backed queues_) desenvolvido para escalar e abstrair o processamento de tarefas pesadas. Projetado especialmente para desopilar rotinas síncronas HTTP, o motor captura os jobs em tabelas e os processa autonomamente usando recursos modernos do **PHP 8.4** através da *Reflection API*.

### 🔥 Principais Funcionalidades
- **PHP 8.4 Native**: Uso exclusivo de *Enums*, Propriedades rigorosamente tipadas e otimizações de base.
- **Tolerância a Falhas**: Sistema automático de _Retry_ e contagem de _Exceptions_. Se um Job falhar por *N* vezes, ele é marcado em erro com o _stack trace_ isolado (Fila Morta).
- **Doctrine DBAL**: Abstração profunda de banco de dados e execução segura de sintaxes (anti-Injection).
- **DotEnv Secure**: Suporte a ambiente blindado para credenciais sensíveis via `.env`.
- **Zero-Config Dashboard**: UI Dark Mode interativa em Realtime inclusa para acompanhamento de filas e seeds na porta principal.

---

## 🛠️ Stack de Tecnologias
- [PHP 8.4](https://www.php.net/) - A linguagem Core.
- [Docker](https://www.docker.com/) & Docker Compose - Orquestração de containers com a imagem `php:8.4-apache` e _MySQL 8+_.
- [Doctrine DBAL](https://www.doctrine-project.org/) - Conexão transparente ao BD e persistência.
- [PHPUnit](https://phpunit.de/) - Cobertura da suíte de Testes.
- [Vlucas/PhpDotenv](https://github.com/vlucas/phpdotenv) - Variáveis de ambiente secretas.

---

## 🚀 Como Iniciar (Setup)

**1. Instale as dependências via Composer**
Se estiver local, baixe os vendors. _(Dentro do ambiente Docker ele pode compartilhar o volume)_.
```bash
cd www/
composer install
```

**2. Configure o Banco de Dados (Ambiente)**
Crie a cópia do seu arquivo DotEnv:
```bash
cp www/.env.example www/.env
```
*(Opcional: preencha as variáveis em `.env` se for rodar externamente. No container ele usa os defaults de root).*

**3. Inicie os Containers**
O projeto acompanha provisionamento ágil contendo o Servidor Web e o Banco de Dados (que roda o script automático e já estrutura a tabela `fila` para você).
```bash
docker compose up -d --build
```

---

## 🖥️ Como Acessar

- **Dashboard Visual**: Abra em seu navegador: `http://localhost:8000`. Aqui você pode rastrear os Jobs e criar instâncias falsas/demonstrativas de fila para teste.
- **Acionar Motor (O Worker)**: Execute esse comando para "Ligar a Esteira". O Daemon ficará lendo o banco a cada 3 segundos infinitamente, buscando resgatar e processar tarefas.
  ```bash
  docker exec -it alessandrois_app php bin/worker.php
  ```

---

## 🏗️ Adicionando na Fila

Para instruir um programa a executar na fila, você não requer instanciar complexos scripts, bastam 5 Linhas:
```php
use AlessandroIsDev\CronJob\Models\FilaModel;
use SuaApp\Jobs\SeuArquivoPesado;

FilaModel::add(
    ['info' => 'Dados gigantes e json payload'], // O Payload base das variaveis
    SeuArquivoPesado::class,                     // A sua classe resolvida 
    'seuMetodoPublico',                          // O método da referida classe
    $arg1, $arg2                                 // N argumentos extras contínuos de função nativa
);
```
O *Worker* automaticamente detectará a classe, vai instanciá-la por _Reflection_ limitando os erros de timeout e isolando instabilidades.

Para entender passo a passo o desenvolvimento arquitetural por trás desta Fila, consulte os cadernos em: `/.dev/passo_a_passo.md`.

---
Feito com 💡 e Código de Alta Performance.