<?php

namespace FpDbTest\QueryBuilder;

/**
 * Подобно Prepare Statement, за исключением того, что подготавливает запрос в памяти и возвращает SQL.
 */
interface QueueStatementInterface 
{
    /** @throws QueryException */
    public function prepare(string $template): QueueStatementInterface;
    
    /**
     * @param mixed[] $params
     * @throws QueryException 
    */
    public function bind(array $params): QueueStatementInterface;

    /** @throws QueryException */
    public function getSql(): string;

    /** Сбрасывает состояние и очищает память*/
    public function close(): void;
}
