<?php

namespace FpDbTest\QueryBuilder\ParamConvertor;

use FpDbTest\QueryBuilder\ConvertorRepositoryInterface;
use FpDbTest\QueryBuilder\QueryException;

class IntegerConvertor extends BaseConvertor
{
    protected function check(): void
    {
        $msgPrefix = 'В параметр типа ' . ConvertorRepositoryInterface::PLACEHOLDER_INT . ' передан';
        $this->checkScalarOrNull($this->param, $msgPrefix);

        if (
            is_float($this->param) 
            && ($this->param > PHP_INT_MAX || $this->param < PHP_INT_MIN)
        ) {
            throw new QueryException($msgPrefix . 'о значение, превышающе границы типа:' .  $this->param);
        }

        if (is_string($this->param)) {
            if (!is_numeric($this->param)) {
                throw new QueryException($msgPrefix . 'о недопустимое значение: ' . $this->param);
            }

            if (
                ((int) $this->param == PHP_INT_MAX && (float) $this->param > PHP_INT_MAX)
                || ((int) $this->param == PHP_INT_MIN && (float) $this->param < PHP_INT_MIN)
            ) {
                throw new QueryException($msgPrefix . 'о значение, превышающе границы типа:' .  $this->param);
            }
        }
    }

    protected function convertParam(): string
    {
        if (is_null($this->param)) {
            return self::NULL_LITERAL;
        }

        return (string) ((int) $this->param);
    }
}
