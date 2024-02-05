<?php

namespace FpDbTest\QueryBuilder\ParamConvertor;

use FpDbTest\QueryBuilder\ConvertorRepositoryInterface;
use FpDbTest\QueryBuilder\QueryException;

class ArrayConvertor extends BaseConvertor
{
    protected function check(): void
    {
        $placeholder = ConvertorRepositoryInterface::PLACEHOLDER_ARRAY;
        $msgPrefix = "В параметр типа $placeholder передан";

        if (!is_array($this->param)) {
            throw new QueryException("$msgPrefix передан недопустимый тип: " . gettype($this->param));
        }

        if (!count($this->param)) {
            throw new QueryException("$msgPrefix передан пустой массив");
        }

        $msgPrefix = "Параметр типа $placeholder в качестве ключа содержит";
        
        if ($this->isAssociative($this->param)) {
            foreach (array_keys($this->param) as $key) {
                if (!$this->isValidId((string) $key)) {
                    throw new QueryException("$msgPrefix недопустимый идентификатор: '$key'");
                }
            }
        }

        $msgPrefix = "Параметр типа $placeholder содержит";

        foreach ($this->param as $val) {
            $this->defaultCheck($val, $msgPrefix);
        }
    }

    protected function convertParam(): string
    {
        $result = [];
        $isAssoc = $this->isAssociative($this->param);
        
        foreach ($this->param as $id => $val) {
            $elm = '';

            if ($isAssoc) {
                $elm = $this->escapeId($id) . ' = ';
            }
             
            $result[] = $elm . $this->convertToSameType($val);
        }

        return implode(', ', $result);
    }
}
