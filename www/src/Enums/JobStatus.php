<?php

namespace AlessandroIsDev\CronJob\Enums;

enum JobStatus: int
{
    case AGUARDANDO_PROCESSAMENTO = 0;
    case PROCESSANDO = 1;
    case SUCESSO = 2;
    case ERRO = 4;
    case AGUARDANDO_REPROCESSAMENTO = 5;
}
