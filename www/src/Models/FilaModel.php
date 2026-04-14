<?php

namespace AlessandroIsDev\CronJob\Models;

use AlessandroIsDev\CronJob\Enums\JobStatus;

class FilaModel extends Model
{
    public static function add(
        mixed  $payload,
        string $classe,
        string $metodo,
               ...$argumentos
    ): false|int|string|null
    {
        return (new self())->save('fila', [
            'status' => JobStatus::AGUARDANDO_PROCESSAMENTO->value,
            'classe' => $classe,
            'metodo' => $metodo,
            'argumentos' => $argumentos,
            'payload' => $payload,
            'tentativas' => 0
        ]);
    }

    public function getJobsToProcess(int $limit = 10): array
    {
        $qb = $this->conn->createQueryBuilder()
            ->select('*')
            ->from('fila')
            ->where('status = :status_novo')
            ->orWhere('status = :status_reprocessar AND updated_at <= DATE_SUB(NOW(), INTERVAL 1 MINUTE)')
            ->setParameter('status_novo', JobStatus::AGUARDANDO_PROCESSAMENTO->value)
            ->setParameter('status_reprocessar', JobStatus::AGUARDANDO_REPROCESSAMENTO->value)
            ->orderBy('id', 'ASC')
            ->setMaxResults($limit);

        return $qb->executeQuery()->fetchAllAssociative();
    }

    public function updateStatus(int $id, JobStatus $status, ?string $log = null): bool
    {
        $data = ['status' => $status->value];
        if ($log !== null) {
            $data['log'] = $log;
        }

        return $this->update('fila', $data, ['id' => $id]);
    }

    public function incrementaTentativa(int $id): bool
    {
        try {
            $this->conn->executeQuery("UPDATE fila SET tentativas = tentativas + 1 WHERE id = ?", [$id]);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getAll(int $limit = 50): array
    {
        return $this->conn->createQueryBuilder()
            ->select('*')
            ->from('fila')
            ->orderBy('created_at', 'DESC')
            ->setMaxResults($limit)
            ->executeQuery()
            ->fetchAllAssociative();
    }

    public function getStats(): array
    {
        $qb = $this->conn->createQueryBuilder()
            ->select('status, COUNT(*) as qtd')
            ->from('fila')
            ->groupBy('status');
            
        $rows = $qb->executeQuery()->fetchAllAssociative();
        $stats = [
            'total' => 0,
            JobStatus::AGUARDANDO_PROCESSAMENTO->value => 0,
            JobStatus::PROCESSANDO->value => 0,
            JobStatus::SUCESSO->value => 0,
            JobStatus::ERRO->value => 0,
            JobStatus::AGUARDANDO_REPROCESSAMENTO->value => 0,
        ];
        
        foreach ($rows as $row) {
            $stats[(int)$row['status']] = (int)$row['qtd'];
            $stats['total'] += (int)$row['qtd'];
        }
        return $stats;
    }
}