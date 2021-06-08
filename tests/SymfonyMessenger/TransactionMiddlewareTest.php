<?php
declare(strict_types=1);

namespace PcComponentes\Transaction\Tests\SymfonyMessenger;

use PcComponentes\Ddd\Application\Query;
use PcComponentes\Transaction\Driver\TransactionalConnection;
use PcComponentes\Transaction\SymfonyMessenger\TransactionMiddleware;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;

final class TransactionMiddlewareTest extends TestCase
{
    /**
     * @test
     */
    public function given_transactional_connection_when_handle_then_begin_transaction_and_commit()
    {
        $transactionalConnection = $this->createMock(TransactionalConnection::class);

        $transactionalConnection
            ->expects($this->once())
            ->method('beginTransaction')
        ;
        $transactionalConnection
            ->expects($this->once())
            ->method('commit')
        ;

        $transactionMiddleware = new TransactionMiddleware($transactionalConnection);
        $transactionMiddleware->handle(
            $this->createMock(Envelope::class),
            $this->createMock(StackInterface::class)
        );
    }

    /**
     * @test
     */
    public function given_envelope_and_stack_with_next_middleware_when_handle_then_go_forward_to_next_middleware_and_execute_it()
    {
        $envelope = $this->createMock(Envelope::class);
        $nextMiddleware = $this->createMock(MiddlewareInterface::class);
        $stack = $this->createMock(StackInterface::class);

        $stack
            ->expects($this->once())
            ->method('next')
            ->willReturn($nextMiddleware)
        ;
        $nextMiddleware
            ->expects($this->once())
            ->method('handle')
            ->with($envelope, $stack)
            ->willReturn($envelope)
        ;

        $transactionMiddleware = new TransactionMiddleware(
            $this->createMock(TransactionalConnection::class)
        );
        $transactionMiddleware->handle(
            $envelope,
            $stack
        );
    }

    /**
     * @test
     */
    public function given_next_middleware_throwing_exception_when_handle_then_rollback_transaction_and_throw_catch_exception()
    {
        $transactionalConnection = $this->createMock(TransactionalConnection::class);
        $nextMiddleware = $this->createMock(MiddlewareInterface::class);
        $stack = $this->createMock(StackInterface::class);
        $exception = new class extends \Exception {};

        $transactionalConnection
            ->expects($this->once())
            ->method('beginTransaction')
        ;
        $stack
            ->expects($this->once())
            ->method('next')
            ->willReturn($nextMiddleware)
        ;
        $nextMiddleware
            ->expects($this->once())
            ->method('handle')
            ->willThrowException($exception)
        ;
        $transactionalConnection
            ->expects($this->never())
            ->method('commit')
        ;
        $transactionalConnection
            ->expects($this->once())
            ->method('rollback')
        ;
        $this->expectException(
            \get_class($exception)
        );

        $transactionMiddleware = new TransactionMiddleware($transactionalConnection);
        $transactionMiddleware->handle(
            $this->createMock(Envelope::class),
            $stack
        );
    }

    /**
     * @test
     */
    public function given_transactional_connection_when_handle_query_message_then_do_nothing()
    {
        $transactionalConnection = $this->createMock(TransactionalConnection::class);
        $envelope = $this->createMock(Envelope::class);
        $queryMessage = $this->createMock(Query::class);

        $envelope
            ->expects($this->once())
            ->method('getMessage')
            ->willReturn($queryMessage);

        $transactionalConnection
            ->expects($this->never())
            ->method('beginTransaction');

        $transactionMiddleware = new TransactionMiddleware($transactionalConnection);
        $transactionMiddleware->handle(
            $envelope,
            $this->createMock(StackInterface::class)
        );
    }
}
