<?php

namespace AlessandroIsDev\CronJob\Models;

class FilaModel extends Model
{
    const STATUS_AGUARDANDO_PROCESSAMENTO = 0;
    const STATUS_PROCESSANDO = 1;
    const STATUS_PROCESSADO = 2;
    const STATUS_ERRO = 4;
    const STATUS_AGUARDANDO_REPROCESSAMENTO = 5;
    const STATUS_REPROCESSANDO = 6;
    const STATUS_REPROCESSADO = 7;

    public static function add(
        mixed  $payload,
        string $classe,
        string $metodo,
               ...$argumentos
    ): false|int|string|null
    {
        return (new self())->save('fila', [
            'status' => self::STATUS_AGUARDANDO_PROCESSAMENTO,
            'classe' => $classe,
            'metodo' => $metodo,
            'argumentos' => $argumentos,
            'payload' => $payload,
        ]);
    }
}