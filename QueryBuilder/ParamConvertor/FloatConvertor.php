<?php

namespace FpDbTest\QueryBuilder\ParamConvertor;

use FpDbTest\QueryBuilder\ConvertorRepositoryInterface;
use FpDbTest\QueryBuilder\QueryException;

class FloatConvertor extends BaseConvertor
{
    protected function check(): void
    {
        $msgPrefix = 'В параметр типа ' . ConvertorRepositoryInterface::PLACEHOLDER_FLOAT . ' передан';
        $this->checkScalarOrNull($this->param, $msgPrefix);

        if (is_string($this->param) && !is_numeric($this->param)) {
            throw new QueryException($msgPrefix . "о недопустимое значение: " . $this->param);
        }

        if (is_infinite((float) $this->param)) {
            throw new QueryException($msgPrefix . 'о значение, превышающе границы типа:' .  $this->param);
        }
    }

    protected function convertParam(): string
    {
        if (is_null($this->param)) {
            return self::NULL_LITERAL;
        }

        return (string) ((float) $this->param);
    }
}
