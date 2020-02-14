<?php
declare(strict_types=1);

namespace PcComponentes\Transaction\Driver;

final class TransactionalConnections implements \Iterator
{
    private array $connections;

    public function __construct(TransactionalConnection ...$connections)
    {
        $this->connections = $connections;
    }

    public function current(): ?TransactionalConnection
    {
        $connection = \current($this->connections);
        if (false === $connection) {
            return null;
        }

        return $connection;
    }

    public function next()
    {
        \next($this->connections);
    }

    public function key()
    {
        return \key($this->connections);
    }

    public function valid(): bool
    {
        return \array_key_exists(
            $this->key(),
            $this->connections
        );
    }

    public function rewind()
    {
        \reset($this->connections);
    }
}
