<?php
declare(strict_types=1);

namespace PcComponentes\Transaction\Driver;

interface TransactionalConnection
{
    public function beginTransaction(): bool;
    public function commit(): bool;
    public function rollBack(): bool;
}
