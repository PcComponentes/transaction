<?php
declare(strict_types=1);

namespace PcComponentes\Transaction\Driver;

interface TransactionalConnection
{
    public function beginTransaction(): void;
    public function commit(): void;
    public function rollBack(): void;
}
