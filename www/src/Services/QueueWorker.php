<?php

namespace AlessandroIsDev\CronJob\Services;

use AlessandroIsDev\CronJob\Enums\JobStatus;
use AlessandroIsDev\CronJob\Models\FilaModel;

class QueueWorker
{
    private int $maxTentativas;

    public function __construct(int $maxTentativas = 3)
    {
        $this->maxTentativas = $maxTentativas;
    }

    public function process(): void
    {
        $filaModel = new FilaModel();
        
        while (true) {
            $jobs = $filaModel->getJobsToProcess(10);
            
            if (empty($jobs)) {
                // Dorme 3 segundos se não houver jobs para não estressar o CPU e BD
                sleep(3);
                continue;
            }

            foreach ($jobs as $job) {
                $filaModel->updateStatus($job['id'], JobStatus::PROCESSANDO);
                
                try {
                    $this->executeJob($job);
                    $filaModel->updateStatus($job['id'], JobStatus::SUCESSO);
                } catch (\Throwable $e) {
                    $filaModel->incrementaTentativa($job['id']);
                    
                    $tentativas = $job['tentativas'] + 1;
                    $statusFail = $tentativas >= $this->maxTentativas 
                                ? JobStatus::ERRO 
                                : JobStatus::AGUARDANDO_REPROCESSAMENTO;
                    
                    $filaModel->updateStatus($job['id'], $statusFail, $e->getMessage());
                    echo "Erro no Job ID {$job['id']}: " . $e->getMessage() . "\n";
                }
            }
        }
    }

    private function executeJob(array $job): void
    {
        $classe = $job['classe'];
        $metodo = $job['metodo'];
        
        $argumentos = json_decode($job['argumentos'], true) ?? [];
        $payload = json_decode($job['payload'], true);
        
        if (!class_exists($classe)) {
            throw new \Exception("Classe não encontrada: $classe");
        }
        
        $instancia = new $classe();
        if (!method_exists($instancia, $metodo)) {
            throw new \Exception("Método não encontrado: {$metodo} na classe {$classe}");
        }
        
        $ReflectionMethod = new \ReflectionMethod($classe, $metodo);
        
        // Injeta o payload como primeiro argumento das rotinas de fila
        $allArgs = array_merge([$payload], $argumentos);
        $ReflectionMethod->invokeArgs($instancia, $allArgs);
    }
}
