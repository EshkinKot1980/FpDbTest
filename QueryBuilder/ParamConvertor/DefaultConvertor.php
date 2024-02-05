<?php

namespace FpDbTest\QueryBuilder\ParamConvertor;

use FpDbTest\QueryBuilder\ConvertorRepositoryInterface;
use FpDbTest\QueryBuilder\QueryException;

class DefaultConvertor extends BaseConvertor
{
    protected function check(): void
    {
        $msgPrefix = 'В параметр типа ' . ConvertorRepositoryInterface::PLACEHOLDER_INT . ' передан';
        $this->defaultCheck($this->param, $msgPrefix);
    }

    protected function convertParam(): string
    {
        return $this->convertToSameType($this->param);
    }
}
