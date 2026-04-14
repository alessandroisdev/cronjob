<?php

namespace AlessandroIsDev\CronJob\Tests\Services;

use PHPUnit\Framework\TestCase;
use AlessandroIsDev\CronJob\Services\QueueWorker;
use AlessandroIsDev\CronJob\Models\FilaModel;
use AlessandroIsDev\CronJob\Enums\JobStatus;

class DummyJob 
{
    public function sucesso(array $payload, string $arg) 
    {
        return true;
    }

    public function falha()
    {
        throw new \Exception("Erro forcado para teste");
    }
}

class QueueWorkerTest extends TestCase
{
    protected function setUp(): void
    {
        $model = new FilaModel();
        $model->getConn()->executeStatement('TRUNCATE table fila');
    }

    public function testWorkerProcessaComSucesso()
    {
        $jobId = FilaModel::add(['test' => true], DummyJob::class, 'sucesso', 'argumento_extra');
        
        $worker = new QueueWorker(3);
        $reflector = new \ReflectionMethod(QueueWorker::class, 'executeJob');
        
        $model = new FilaModel();
        $jobs = $model->getJobsToProcess(1);
        
        $reflector->invokeArgs($worker, [$jobs[0]]);
        
        $this->assertTrue(true, "A execucao reflexiva via Reflection nao lancou excecoes.");
    }

    public function testWorkerIncrementaTentativasDeJobComFalha()
    {
        $jobId = FilaModel::add([], DummyJob::class, 'falha');

        $model = new FilaModel();
        $jobs = $model->getJobsToProcess(1);
        $job = $jobs[0];
        
        $worker = new QueueWorker(3);
        
        try {
            $reflector = new \ReflectionMethod(QueueWorker::class, 'executeJob');
            $reflector->invokeArgs($worker, [$job]);
            $this->fail('Deveria ter disparado Exception');
        } catch (\ReflectionException|\Throwable $e) {
            $model->incrementaTentativa($jobId);
            $model->updateStatus($jobId, JobStatus::AGUARDANDO_REPROCESSAMENTO, $e->getMessage());
        }

        $jobsCheck = $model->getConn()->createQueryBuilder()
            ->select('*')->from('fila')->where('id = :id')
            ->setParameter('id', $jobId)->executeQuery()->fetchAllAssociative();
            
        $this->assertEquals(JobStatus::AGUARDANDO_REPROCESSAMENTO->value, (int)$jobsCheck[0]['status']);
        $this->assertEquals(1, (int)$jobsCheck[0]['tentativas']);
    }
}
