<?php
declare(strict_types=1);

namespace PcComponentes\Transaction\SymfonyMessenger;

use PcComponentes\Ddd\Application\Query;
use PcComponentes\Transaction\Driver\TransactionalConnection;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;

final class TransactionMiddleware implements MiddlewareInterface
{
    private TransactionalConnection $connection;

    public function __construct(TransactionalConnection $connection)
    {
        $this->connection = $connection;
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        if (\is_subclass_of($envelope->getMessage(), Query::class)) {
            return $stack->next()->handle($envelope, $stack);
        }

        try {
            $this->connection->beginTransaction();
            $envelope = $stack->next()->handle($envelope, $stack);
            $this->connection->commit();

            return $envelope;
        } catch (\Throwable $exception) {
            if ($this->connection->isTransactionActive()) {
                $this->connection->rollBack();
            }

            throw $exception;
        }
    }
}
