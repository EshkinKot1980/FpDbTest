<?php

namespace FpDbTest\QueryBuilder\ParamConvertor;

use FpDbTest\QueryBuilder\ParamConvertorInterface;
use FpDbTest\QueryBuilder\QueryException;

abstract class BaseConvertor implements ParamConvertorInterface
{
    protected const NULL_LITERAL = 'NULL';
    private const ID_VALID_CANDIDAT_REGEXP = '/^[0-9a-zA-Z\$_]{1,64}(?:\.[0-9a-zA-Z\$_]{1,64})*$/';
    private const ID_INVALID_EXPR_REGEXP = '/(?:^|\.)\d{1,64}(?:\.|$)/';

    /** @var string|array|int|float|bool|null */
    protected $param;
    
    public function convert($param): string
    {
        $this->param = $param;
        $this->check();
        $result = $this->convertParam();
        $this->param = null;

        return $result;
    }

    /** @thows QueryException */
    abstract protected function check():void;
    abstract protected function convertParam():string;

    protected function escapeString(string $str): string
    {
        return str_replace(
            ["\\", "'", '"', "\x00", "\n", "\r", "\x1a"],
            ["\\\\", "\'", '\"', "\\x00", "\\n", "\\r", "\\x1a"],
            $str
        );
    }

    protected function isAssociative(array &$arr): bool
    {
        foreach (array_keys($arr) as $key) {
            if (is_string($key)) {
                return true;
            }
        }

        return false;
    }

    protected function isValidId(string $id): bool
    {
        return preg_match(self::ID_VALID_CANDIDAT_REGEXP, $id) && !preg_match(self::ID_INVALID_EXPR_REGEXP, $id);
    }

    protected function escapeId(string $id): string
    {
        $idParts = explode('.', $id);

        return '`' . implode('`.`', $idParts) . '`';
    }

    /** @param string|int|float|bool|null $param */
    protected function convertToSameType($param): string
    {
        if (is_null($param)) {
            return self::NULL_LITERAL;
        }

        if (is_bool($param)) {
            return (string) ((int) $param);
        }

        if (is_string($param)) {
            return "'" . $this->escapeString($param) . "'";
        }

        return (string) $param;
    }

    /** @param mixed $param */
    protected function defaultCheck($param, string $msgPrefix): void
    {
        $this->checkScalarOrNull($param, $msgPrefix);

        if (is_float($param) && is_infinite($param)) {
            throw new QueryException("$msgPrefix float, превышающий допустимые границы типа");
        }
    }

    /** @param mixed $param */
    protected function checkScalarOrNull($param,string $msgPrefix): void
    {
        if (!is_scalar($param) && !is_null($param)) {
            throw new QueryException("$msgPrefix недопустимый тип: " . gettype($this->param));
        } 
    }
}
