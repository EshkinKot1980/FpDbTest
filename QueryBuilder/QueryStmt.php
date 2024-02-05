<?php

namespace FpDbTest\QueryBuilder;

class QueryStmt implements QueueStatementInterface
{    
    private const BLOCK_SPLIT_REGEXP = '/(\{[^\}]*\})/u';
    private const BLOCK_CHECK_REGEXP = '/^\{([^\{]*(\{)*[^\{]*)\}$/';
    private const NOT_BLOCK_CHECK_REGEXP = '/(\{|\})/';
    private const PARAM_REGEXP = '/\{\{(\d+)\}\}/u';
    
    private ConvertorRepositoryInterface $repository;
    private string $template;
    private array $params;
    private array $queryParts;
    private array $blockKeys;
    private array $paramsMap;
    private array $placeholders;
    private bool $isPrepared = false;
    private string $parameterizedSql = '';
    private ?string $sql = null;

    public function __construct(ConvertorRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }
    
    public function prepare(string $template): QueueStatementInterface
    {
        $this->template = $this->normalizeTemplate($template);
        $this->sql = null;
        $this->isPrepared = false;
        
        $this
            ->splitTemplate()
            ->checkBlocksSyntax()
            ->mapParams();

        $this->isPrepared = true;

        return $this;
    }

    public function bind(array $params): QueueStatementInterface
    {
        if (!$this->isPrepared) {
            throw new QueryException('Шаблон не подготовлен, воспользутесь методом prepare.');
        }

        if (count($params) != count($this->placeholders)) {
            throw new QueryException('Количество переданных параметром не совпадает с количеством параметров в шаблоне.');
        }

        $this->params = $params;

        $this
            ->makeResultTemplate()
            ->substituteParams();

        return $this;
    }

    public function getSql(): string
    {
        if (is_null($this->sql)) {
            throw new QueryException('Итоговый SQL не сформирован');
        }

        return $this->sql;
    }

    public function close(): void
    {
        $this->template = '';
        $this->params = [];
        $this->queryParts = [];
        $this->blockKeys = [];
        $this->paramsMap = [];
        $this->placeholders = [];
        $this->isPrepared = false;
        $this->parameterizedSql = '';
        $this->sql = null;
    }

    private function normalizeTemplate(string $template): string
    {
        return str_replace(
            ["\n\r", "\n", "\r"],
            ' ',
            $template
        );
    }

    private function splitTemplate(): self
    {
        $this->queryParts = preg_split(
            self::BLOCK_SPLIT_REGEXP,
            $this->template,
            -1,
            PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE
        );

        return $this;
    }

    private function checkBlocksSyntax(): self
    {
        $this->blockKeys = [];
        
        foreach ($this->queryParts as $i => $part) {
            if (preg_match(self::BLOCK_CHECK_REGEXP, $part, $m)) {
                if(count($m) > 2) {
                    throw new QueryException('Шаблоны не могут быть вложенными: ' . $part);
                }

                $this->queryParts[$i] = $m[1];
                $this->blockKeys[] = $i;
            } elseif(preg_match(self::NOT_BLOCK_CHECK_REGEXP, $part, $m)) {
                throw new QueryException('Неожиданное вхождение ' . $m[1] . ' в блоке: ' . $part);
            }
        }

        return $this;
    }

    private function mapParams(): self
    {
        $this->paramsMap = [];
        $this->placeholders = [];
        $paramKey = 0;

        foreach ($this->queryParts as $i => $part) {
            $this->paramsMap[$i] = [];

            $this->queryParts[$i] = preg_replace_callback(
                ConvertorRepositoryInterface::PLACEHOLDER_REGEXP,
                function($m) use (&$paramKey, $i) {
                    $this->paramsMap[$i][] = $paramKey;
                    $this->placeholders[$paramKey] = $m[0];
                    
                    return '{{' . ($paramKey++) . '}}';
                },
                $part
            );
        }
        
        return $this;
    }

    private function makeResultTemplate(): self
    {
        $this->parameterizedSql = '';
        foreach ($this->queryParts as $i => $part) {
            $skip = false;
            
            if (in_array($i, $this->blockKeys)) {
                $paramKeys = $this->paramsMap[$i];

                foreach($paramKeys as $key) {
                    if ($this->params[$key] instanceof SkipInterface) {
                        $skip = true;
                    }
                }
            }

            $this->parameterizedSql .= $skip ? '' : $part;
        }
        
        return $this;
    }

    private function substituteParams(): self
    {
        $this->sql = preg_replace_callback(
            self::PARAM_REGEXP,
            function($m) {
                $i = (int)$m[1];

                return $this->repository
                    ->getByPlaceholder($this->placeholders[$i])
                    ->convert($this->params[$i]);
            },
            $this->parameterizedSql
        );
        
        $this->parameterizedSql = '';
        
        return $this;
    }
}
