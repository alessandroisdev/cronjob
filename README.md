<div align="center">
  <img src="https://img.shields.io/badge/PHP-8.4-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.4" />
  <img src="https://img.shields.io/badge/Docker-2CA5E0?style=for-the-badge&logo=docker&logoColor=white" alt="Docker" />
  <img src="https://img.shields.io/badge/Doctrine-DBAL-F68D2E?style=for-the-badge&logo=php&logoColor=white" alt="Doctrine" />
  <img src="https://img.shields.io/badge/License-MIT-green?style=for-the-badge" alt="MIT License" />

  <h2>🌟 CronJob Engine Pro</h2>
  <p><strong>Motor assíncrono de processamento de filas focado em alta performance e resiliência.</strong></p>
</div>

<hr />

## 📖 Visão Geral

O **CronJob Engine** é uma implementação robusta e autônoma desenvolvida em **PHP 8.4** para gerenciamento e execução de filas baseadas em banco de dados (*Database-backed Queues*). 

Seu propósito é isolar tarefas pesadas — como envio em massa de e-mails, processos complexos de integração governamental, relatórios PDF massivos e conexões com APIs lentas — garantindo que a aplicação web principal (Frontend/API) responda imediatamente ao cliente.

### ✨ Por Que Usar Este Motor?
* **Zero Timeout**: As requisições HTTP do seu cliente não travam mais. O trabalho longo é delegado para o banco em menos de *1ms*.
* **Tolerância a Falhas Dinâmica (Retry Inteligente)**: O motor captura intermitências (como quedas temporárias de rede do SMTP). Ele processará automaticamente a mesma tarefa até 3 vezes. Caso ultrapasse a resiliência, encaminha o job para a Fila Morta (Status de Erro Absoluto) anexando a Stack Trace completa do problema.
* **PHP 8.4 Native**: Uso sofisticado do estado da arte do PHP (Enums fortemente tipados, propriedades modernas e injeções puras).
* **Integração Agnóstica**: Funciona independente do ecossistema principal do seu app e framework. Basta invocar a `FilaModel::add`.

---

## 🏗️ Arquitetura do Sistema

O motor opera em modelo assíncrono. Enquanto as classes adicionam requisições na tabela num piscar de olhos, paralelamente um processo paralelo em _Loop Contínuo_ (O `QueueWorker`) captura as pendências, e utilizando a **Reflection API**, desperta inteligentemente os referidos arquivos e funções para o consumo.

```text
cronjob/
├── .docker/             # Configurações de virtualização e setup automático (Schema MySQL)
├── www/
│   ├── .env             # Mapeamento blindado de credenciais de serviço
│   ├── src/             
│   │   ├── Enums/       # Status rigorosos da fila de mensageria (Processando, Aguardando, Erro)
│   │   ├── Models/      # Camada Doctrine DBAL de persistência e Queries 
│   │   ├── Services/    # O Core do QueueWorker que orquestra a Reflection
│   │   └── Jobs/        # Repositório das lógicas demoradas a serem processadas
│   ├── bin/             # Daemon executável em terminal local (script worker infinito)
│   └── tests/           # Suíte nativa para cobertura automatizada (PHPUnit)
└── ...
```

---

## 🚀 Guia Prático de Instalação (Deploy)

O ambiente é 100% conteinerizado usando Docker, exigindo zero configuração manual para ligar o Motor de Filas e o Serviço de Database dedicado.

**1. Clone o repositório e configure as variáveis de segurança:**
```bash
git clone https://github.com/alessandroisdev/cronjob.git
cd cronjob/www
cp .env.example .env
```

**2. Levante o Ecossistema Computacional (Docker Compose):**
```bash
cd ..
docker compose up -d --build
```
*Nota: Graças ao volume otimizado no boot, o banco criará autonomamente caso nunca o tenha feito a estrutura íntegra da tabela (`schema.sql`).*

**3. Autoload via Composer:**
Garante que o diretório _vendor_ contenha todas as premissas como Doctrine e PHPUnit.
```bash
docker exec -it alessandrois_app composer install
```

---

## 💻 Usabilidade na Prática (Para Devs)

Adicionar novas rotinas na fila é um aspecto projetado para ser limpo e fluído, quase sem verbosidade de código.

### Passo 1: Construindo sua Classe Alvo Isolada
Qualquer rotina pesada deve estar agrupada numa classe regular com funções públicas, esperando primariamente os parâmetros do seu Payload em forma associativa (Array).
```php
namespace App\Jobs;

class EmissaoNotaExterna 
{
    public function gerarEmissao(array $payload, bool $forcarAutorizacao = false) 
    {
        // Lógica de comunicação Sefaz ou Gateways durando de 5 a 10s aqui...
    }
}
```

### Passo 2: O Agente Despachante
No mesmo segundo em que a requisição adentra a controladora da sua API/Frontend, faça a sua injeção:
```php
use AlessandroIsDev\CronJob\Models\FilaModel;
use App\Jobs\EmissaoNotaExterna;

FilaModel::add(
    ['num_comprador' => 9991230, 'valor' => 1500.00], 
    EmissaoNotaExterna::class,                    // FQCN detectado por escopo       
    'gerarEmissao',                               // Nome do referido método público
    true                                          // Modificadores opcionais da assinatura do pacote
);
```

### Passo 3: O Ligamento (Pelo ambiente Servidor)
Dê a partida no Worker para ele trabalhar perpetamente via Terminal / CRON ou Supervisor linux:
```bash
docker exec -d alessandrois_app php bin/worker.php
```

---

## 📊 Dashboard UI e Monitoria
Este pacote acompanha uma Interface Limpida projetada em *Dark Mode*.
Acesse **`http://localhost:8000/`** no seu navegador para verificar um painel consolidando KPIs com relatórios anexos de instabilidades, lógicas de retentativas visualizadas em Real-Time e falhas fatais que exigem resolução humana. Nenhuma linha de terminal precisa ser lida. Tudo está transparente.

---

## 🎯 Suíte de Testes (TDD)
Mantemos robustez e segurança arquitetural extrema. Verifique a resiliência a exaustões executando toda a bateria estritamente avaliada sobre Exceptions e Loops:
```bash
docker exec -it alessandrois_app ./vendor/bin/phpunit
```

<hr />

<div align="center">
  <sub>App Architecture • Performance • Open-Source Code </sub><br />
  <sub>Copyright © Alessandro P Souza </sub>
</div>