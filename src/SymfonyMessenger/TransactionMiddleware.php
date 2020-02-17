<?php
declare(strict_types=1);

namespace PcComponentes\Transaction\SymfonyMessenger;

use PcComponentes\Transaction\Driver\TransactionalConnection;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;

final class TransactionMiddleware implements MiddlewareInterface
{
    private TransactionalConnection $connection;

    public function __construct(TransactionalConnection $connection)
    {
        $this->connection = $connection;
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        try {
            $this->connection->beginTransaction();
            $envelope = $stack->next()->handle($envelope, $stack);
            $this->connection->commit();

            return $envelope;
        } catch (\Throwable $exception) {
            $this->connection->rollBack();

            throw $exception;
        }
    }
}
