<?php

namespace FpDbTest\QueryBuilder;

use FpDbTest\QueryBuilder\ParamConvertor\ArrayConvertor;
use FpDbTest\QueryBuilder\ParamConvertor\DefaultConvertor;
use FpDbTest\QueryBuilder\ParamConvertor\FloatConvertor;
use FpDbTest\QueryBuilder\ParamConvertor\IdentifierConvertor;
use FpDbTest\QueryBuilder\ParamConvertor\IntegerConvertor;

class ConvertorRepository implements ConvertorRepositoryInterface
{
    /** @var ParamConvertorInterface[] */
    private array $convertorPool = [];
    
    public function getByPlaceholder(string $placeholder): ParamConvertorInterface
    {
        if (array_key_exists($placeholder, $this->convertorPool)) {
            return $this->convertorPool[$placeholder];
        }
        
        $convertor = $this->makeConvertor($placeholder);
        $this->convertorPool[$placeholder] = $convertor;

        return $convertor;
    }

    private function makeConvertor(string $placeholder): paramConvertorInterface
    {
        switch ($placeholder) {
            case self::PLACEHOLDER_DEFAULT:
                return new DefaultConvertor();
            case self::PLACEHOLDER_INT:
                return new IntegerConvertor();
            case self::PLACEHOLDER_FLOAT:
                return new FloatConvertor();
            case self::PLACEHOLDER_ID:
                return new IdentifierConvertor();
            case self::PLACEHOLDER_ARRAY:
                return new ArrayConvertor();
            default:
                throw new QueryException("Передан неизвестный заполнитель: '$placeholder'");
        }
    }
}
