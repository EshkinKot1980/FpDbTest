<?php

namespace FpDbTest\QueryBuilder;

interface ParamConvertorInterface
{
    /**
     * @param string|array|int|float|bool|null $param
     * @throws QueryException
     */
    public function convert($param): string;
}
