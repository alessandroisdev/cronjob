<?php

require_once __DIR__ . '/../vendor/autoload.php';

use AlessandroIsDev\CronJob\Services\QueueWorker;

echo "Iniciando Gerenciador de Filas (Queue Worker) em PHP 8.4...\n";
echo "Pressione Ctrl+C para encerrar.\n";

$worker = new QueueWorker(3);
$worker->process();
