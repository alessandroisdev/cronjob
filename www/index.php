<?php

require_once __DIR__ . '/vendor/autoload.php';

use AlessandroIsDev\CronJob\Models\FilaModel;
use AlessandroIsDev\CronJob\Enums\JobStatus;

$model = new FilaModel();


$stats = $model->getStats();
$jobs = $model->getAll(100);

function formatStatus(int $status): string {
    return match ($status) {
        JobStatus::AGUARDANDO_PROCESSAMENTO->value => '<span class="status-badge status-waiting">Aguardando</span>',
        JobStatus::PROCESSANDO->value => '<span class="status-badge status-processing">Processando</span>',
        JobStatus::SUCESSO->value => '<span class="status-badge status-success">Sucesso</span>',
        JobStatus::ERRO->value => '<span class="status-badge status-error">Falha Absoluta</span>',
        JobStatus::AGUARDANDO_REPROCESSAMENTO->value => '<span class="status-badge status-retrying">Reprocessando</span>',
        default => '<span class="status-badge">Desconhecido</span>',
    };
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CronJob Dashboard Pro</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600&family=JetBrains+Mono:wght@400&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-base: #0f172a;
            --bg-card: #1e293b;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --accent: #3b82f6;
            --border: #334155;
            
            --success: #10b981;
            --error: #ef4444;
            --processing: #f59e0b;
            --idle: #64748b;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg-base);
            color: var(--text-main);
            margin: 0;
            padding: 2rem;
            min-height: 100vh;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border);
        }

        h1 {
            font-size: 2rem;
            font-weight: 600;
            margin: 0;
            background: linear-gradient(to right, #38bdf8, #818cf8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            transition: transform 0.2s ease;
        }

        .stat-card:hover {
            transform: translateY(-2px);
        }

        .stat-title {
            font-size: 0.875rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.5rem;
        }

        .stat-value {
            font-size: 2.5rem;
            font-weight: 600;
            line-height: 1;
        }

        .val-success { color: var(--success); }
        .val-error { color: var(--error); }
        .val-processing { color: var(--processing); }
        
        .table-container {
            background: var(--bg-card);
            border-radius: 12px;
            border: 1px solid var(--border);
            overflow: hidden;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        th, td {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--border);
        }

        th {
            background: rgba(15, 23, 42, 0.4);
            font-weight: 600;
            font-size: 0.875rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        td {
            font-size: 0.95rem;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:hover td {
            background: rgba(51, 65, 85, 0.3);
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            background: var(--border);
        }

        .status-success { background: rgba(16, 185, 129, 0.1); color: var(--success); border: 1px solid rgba(16, 185, 129, 0.3); }
        .status-error { background: rgba(239, 68, 68, 0.1); color: var(--error); border: 1px solid rgba(239, 68, 68, 0.3); }
        .status-processing { background: rgba(245, 158, 11, 0.1); color: var(--processing); border: 1px solid rgba(245, 158, 11, 0.3); }
        .status-waiting { background: rgba(148, 163, 184, 0.1); color: var(--text-muted); }
        .status-retrying { background: rgba(59, 130, 246, 0.1); color: var(--accent); }

        .mono {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.85rem;
            color: #c4b5fd;
        }
        
        .empty-state {
            padding: 4rem;
            text-align: center;
            color: var(--text-muted);
        }
    </style>
    <!-- Auto-reload a cada 10s para acompanhar processamentos in realtime visualmente -->
    <meta http-equiv="refresh" content="10">
</head>
<body>
    <div class="container">
        <header>
            <h1>Queue Dashboard</h1>
            <div>
                <a href="#" style="color:var(--text-muted); text-decoration:none; font-weight: 600;">Auto-Refresh 10s</a>
            </div>
        </header>

        <div class="stats-grid">
            <div class="stat-card">
                <span class="stat-title">Total de Jobs</span>
                <span class="stat-value"><?= $stats['total'] ?></span>
            </div>
            <div class="stat-card">
                <span class="stat-title">Na Fila</span>
                <span class="stat-value"><?= $stats[JobStatus::AGUARDANDO_PROCESSAMENTO->value] ?></span>
            </div>
            <div class="stat-card">
                <span class="stat-title">Em Execução</span>
                <span class="stat-value val-processing"><?= $stats[JobStatus::PROCESSANDO->value] ?></span>
            </div>
            <div class="stat-card">
                <span class="stat-title">Completados</span>
                <span class="stat-value val-success"><?= $stats[JobStatus::SUCESSO->value] ?></span>
            </div>
            <div class="stat-card">
                <span class="stat-title">Falhas / Erros</span>
                <span class="stat-value val-error"><?= $stats[JobStatus::ERRO->value] ?></span>
            </div>
        </div>

        <div class="table-container">
            <?php if (empty($jobs)): ?>
                <div class="empty-state">
                    <h3>Nenhuma tarefa registrada.</h3>
                    <p>Você pode testar inserindo um Job via código para ele aparecer aqui.</p>
                </div>
            <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Target</th>
                        <th>Status</th>
                        <th>Payload</th>
                        <th>Tentativas</th>
                        <th style="max-width: 300px;">Logs/Erros</th>
                        <th>Modificado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($jobs as $job): ?>
                    <tr>
                        <td class="mono">#<?= $job['id'] ?></td>
                        <td>
                            <strong style="display:block;margin-bottom:4px;"><?= htmlspecialchars($job['classe']) ?></strong>
                            <span style="color:var(--text-muted);font-size:0.8rem;">::<?= htmlspecialchars($job['metodo']) ?>()</span>
                        </td>
                        <td><?= formatStatus((int)$job['status']) ?></td>
                        <td class="mono" style="font-size:0.7rem; max-width: 250px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            <?= htmlspecialchars($job['payload'] ?? 'null') ?>
                        </td>
                        <td><?= $job['tentativas'] ?>/3</td>
                        <td style="max-width:250px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; font-size:0.8rem; color:var(--text-muted);">
                            <?= htmlspecialchars($job['log'] ?? '-') ?>
                        </td>
                        <td style="font-size:0.85rem; color:var(--text-muted);"><?= date('H:i:s d/m', strtotime($job['updated_at'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
