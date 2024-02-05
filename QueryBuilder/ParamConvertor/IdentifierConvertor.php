<?php

namespace FpDbTest\QueryBuilder\ParamConvertor;

use FpDbTest\QueryBuilder\ConvertorRepositoryInterface;
use FpDbTest\QueryBuilder\QueryException;

class IdentifierConvertor extends BaseConvertor
{
    protected function check(): void
    {
        $placeholder = ConvertorRepositoryInterface::PLACEHOLDER_ID;
        $msgPrefix = "В параметр типа $placeholder передан";

        if (!is_string($this->param) && !is_array($this->param)) {
            throw new QueryException("$msgPrefix недопустимый тип: " . gettype($this->param));
        }

        if (is_array($this->param)) {
            if (!count($this->param)) {
                throw new QueryException("$msgPrefix пустой массив");
            }
        } else {
            $this->param = [$this->param];
        }

        $msgPrefix = "Параметр типа $placeholder содержит";

        foreach ($this->param as $id) {
            if (!is_string($id)) {
                throw new QueryException("$msgPrefix недопустимый тип: " . gettype($id));
            }

            if (!$this->isValidId($id)) {
                throw new QueryException("$msgPrefix недопустимый идентификатор: " . $id);
            }
        }
    }

    protected function convertParam(): string
    {
        $result = [];
        
        foreach ($this->param as $id ) {
            $result[] = $this->escapeId($id);
        }

        return implode(', ', $result);
    }
}
