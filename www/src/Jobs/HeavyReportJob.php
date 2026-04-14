<?php

namespace AlessandroIsDev\CronJob\Jobs;

class HeavyReportJob
{
    public function generate(array $payload, int $formato)
    {
        $rows = $payload['rows'] ?? 1000;
        
        // Simula cálculo muito custoso (Irá demorar uns bons segundos sendo processado na fila)
        sleep(3);
        
        return "Relatório com $rows linhas gerado no formato $formato!";
    }
}
