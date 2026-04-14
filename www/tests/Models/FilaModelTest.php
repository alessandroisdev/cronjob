<?php

namespace AlessandroIsDev\CronJob\Tests\Models;

use PHPUnit\Framework\TestCase;
use AlessandroIsDev\CronJob\Models\FilaModel;
use AlessandroIsDev\CronJob\Enums\JobStatus;

class FilaModelTest extends TestCase
{
    public function testAddJobInsertsIntoDatabaseAndDefaultsToAguardando()
    {
        $model = new FilaModel();
        $model->getConn()->executeStatement('TRUNCATE table fila');

        $jobId = FilaModel::add(['email' => 'teste@teste.com'], 'EnvioEmail', 'enviar', 'urgente');

        $this->assertIsNumeric($jobId);

        $jobs = $model->getJobsToProcess(1);
        $this->assertCount(1, $jobs);
        
        $job = $jobs[0];
        $this->assertEquals(JobStatus::AGUARDANDO_PROCESSAMENTO->value, (int) $job['status']);
        $this->assertEquals('EnvioEmail', $job['classe']);
        $this->assertEquals('enviar', $job['metodo']);
    }
}
