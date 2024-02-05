<?php

namespace FpDbTest;

use FpDbTest\QueryBuilder\QueryException;
use FpDbTest\QueryBuilder\QueueStatementInterface;
use FpDbTest\QueryBuilder\SkipInterface;
use mysqli;

class Database implements DatabaseInterface
{
    private mysqli $mysqli;
    private QueueStatementInterface $stmt;


    public function __construct(mysqli $mysqli, QueueStatementInterface $stmt)
    {
        $this->mysqli = $mysqli;
        $this->stmt = $stmt;
    }

    public function buildQuery(string $query, array $args = []): string
    {
        try {
            $sql = $this->stmt
                ->prepare($query)
                ->bind($args)
                ->getSql();

            $this->stmt->close();
        } catch (QueryException $e) {
            throw $e
                ->setTemplate($query)
                ->setParams($args);
        }
        
        return $sql;
    }

    public function skip(): SkipInterface
    {
        return new class implements SkipInterface {};
    }
}
