# Documentação: Gerenciador de Filas (CronJob)

Bem-vindo à engenharia do nosso próprio motor de filas! Desenvolvido em **PHP 8.4** com Doctrine, o objetivo principal deste pacote é encapsular requisições pesadas ou assíncronas do usuário para evitar _timeouts_ e demoras excessivas nas páginas do seu projeto original.

---

## 1. O que ele Faz?

Em vez de enviar um e-mail longo ou gerar um PDF dentro da requisição principal de um cliente web do seu sistema (o que faria o cliente ficar esperando em uma tela de _Loading_ demorada), nós jogamos o "pedido" para o Banco de Dados. Isso demora 1 milissegundo.

Lá no fundo, protegido de qualquer interface, temos um processo robô executando num loop infinito (`o Worker`) que caçará esses pedidos no Banco de Dados a cada 3 segundos, instanciando magicamente (via _Reflection API_) a classe programada e realizando o trabalho sujo.
Se o trabalho der erro, ele re-agenda. Se errar 3 vezes, ele desiste e marca o banco de dados reportando qual foi o erro técnico real no `log`.

---

## 2. Passo-a-passo: Como Usar?

Vamos detalhar a prática do dia-a-dia para o desenvolvedor ao adicionar novas rotinas na Fila.

### Passo A: Criar sua classe do Job
Tudo que vai para a fila deve estar contido numa Classe e um Método. Vamos inventar uma tarefa chamada `ProcessarPagamento`.
Você criaria ela no diretório ou pacote do seu sistema principal (No nosso caso, está em `src/Jobs`):

```php
namespace AlessandroIsDev\CronJob\Jobs;

class PagamentoJob 
{
    // Crie o método principal com qualquer nome. O primeiro argumento obrigatoriamente
    // receberá o Array Associativo com todos os DADOS DA REAQUISIÇÃO (Payload).
    // Eventuais outros argumentos podem ser tipados a seguir.
    public function debitar(array $payload, int $urgencia, string $moeda) 
    {
        $usuarioId = $payload['usuario_id'];
        $valor = $payload['valor'];
        
        // Simular ida numa API externa LENTA
        sleep(3); 
        
        if ($valor > 1000) {
            // Toda exceção natural será ouvida pelo nosso Worker
            // e registrará este texto abaixo nos registros de log detalhados!
            throw new \Exception("Cartão declinado por limite de segurança do Banco X."); 
        }

        return "Pagamento efetuado.";
    }
}
```

### Passo B: Agendar o processo na Controller
Na sua aplicação Web que recebe os dados (seja um Framework qualquer, e aqui no projeto usamos apenas o `FilaModel::add`), chame o motor de entrada em background. Todo o trabalho passa pelo **FilaModel::add**!

```php
use AlessandroIsDev\CronJob\Models\FilaModel;
use AlessandroIsDev\CronJob\Jobs\PagamentoJob;

// Exemplo da Rota (/checkout)

// O FilaModel::add recebe:
// 1. Array do Payload (todos os seus dados importantes)
// 2. FQCN (Nome completo com o namespace da classe) 
// 3. O nome do Método da referida classe
// 4 e adiante... Os Argumentos nativos em sequência ($urgencia, $moeda).

FilaModel::add(
    ['usuario_id' => 155, 'valor' => 1500.00], // Payload base
    PagamentoJob::class,                       // Classe (Dica: sempre use ::class para o php autocompletar)
    'debitar',                                 // Metodo Alvo
    99,                                        // $urgencia
    'BRL'                                      // $moeda
);

echo "Pedido registrado com sucesso! Você será notificado.";
```
Nesta hora, o *status* da fila nasce no banco como **Enum 0 (Aguardando Processamento)**.

---

## 3. Rodando o Motor (Ligando a Esteira)

Até o "Passo B", nada rodou. As tarefas estão empilhando no seu banco.
Você precisa iniciar o _QueueWorker_ para trabalhar!

Abra o Terminal base do seu servidor (ou conecte via SSH) ou simplesmente vá no console do Docker do seu projeto:

```bash
docker exec -it alessandrois_app php bin/worker.php
```
`Nota em Produção`: Geralmente usamos o `Supervisor` do Linux rodando este script e garantindo que ele reviverá automaticamente caso acabe caindo.

A tela exibirá a mágica rodando: o servidor irá ler o Banco de Dados, puxar as filas pendentes ou em reprocessamento e executar! Sincronize visualmente os status mudando na nossa URL `http://localhost:8000` (Painel Web). 
