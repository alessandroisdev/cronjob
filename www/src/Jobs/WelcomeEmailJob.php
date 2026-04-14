<?php

namespace AlessandroIsDev\CronJob\Jobs;

class WelcomeEmailJob
{
    public function send(array $payload, string $priority = "normal")
    {
        $to = $payload['email'] ?? 'desconhecido@teste.com';
        $name = $payload['nome'] ?? 'Usuário';
        
        // Simula processamento
        sleep(1); 
        
        return true;
    }
}
