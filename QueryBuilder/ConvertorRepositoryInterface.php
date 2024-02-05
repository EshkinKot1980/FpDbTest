<?php

namespace FpDbTest\QueryBuilder;

interface ConvertorRepositoryInterface
{
    const PLACEHOLDER_REGEXP = '/(\?[#afd]?)/u';
    
    const PLACEHOLDER_DEFAULT = '?';
    const PLACEHOLDER_ID = '?#';
    const PLACEHOLDER_ARRAY = '?a';
    const PLACEHOLDER_FLOAT = '?f';
    const PLACEHOLDER_INT = '?d';

    /** @throws QueryException */
    public function getByPlaceholder(string $placeholder): ParamConvertorInterface;
}
