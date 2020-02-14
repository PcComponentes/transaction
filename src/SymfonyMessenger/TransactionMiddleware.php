<?php
declare(strict_types=1);

namespace PcComponentes\Transaction\SymfonyMessenger;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\StackInterface;
use PcComponentes\Transaction\Driver\TransactionalConnections;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;

final class TransactionMiddleware implements MiddlewareInterface
{
    private TransactionalConnections $connections;

    public function __construct(TransactionalConnections $connections)
    {
        $this->connections = $connections;
    }
    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        try {
            $this->beginTransaction();
            $envelope = $stack->next()->handle($envelope, $stack);
            $this->commit();

            return $envelope;
        } catch (\Throwable $exception) {
            $this->rollBack();

            throw $exception;
        }
    }

    private function beginTransaction(): void
    {
        foreach ($this->connections as $connection) {
            $connection->beginTransaction();
        }
    }

    private function commit(): void
    {
        foreach ($this->connections as $connection) {
            $connection->commit();
        }
    }

    private function rollBack(): void
    {
        foreach ($this->connections as $connection) {
            $connection->rollBack();
        }
    }
}
