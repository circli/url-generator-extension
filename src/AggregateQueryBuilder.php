<?php declare(strict_types=1);

namespace Circli\Extensions\UrlGenerator;

class AggregateQueryBuilder implements QueryStringBuilder
{
    /** @var QueryStringBuilder[] */
    private $queryBuilders = [];

    public function __construct(QueryStringBuilder $queryBuilder)
    {
        $this->queryBuilders[] = $queryBuilder;
    }

    public function add(QueryStringBuilder $queryBuilder): self
    {
        $this->queryBuilders[] = $queryBuilder;

        return $this;
    }

    public function addQuery(string $key, $value): self
    {
        return $this->add(SimpleQueryStringBuilder::fromArray([$key => $value]));
    }

    public function getQuery(): array
    {
        $query = [];
        foreach ($this->queryBuilders as $builder) {
            $query[] = $builder->getQuery();
        }

        return array_merge(...$query);
    }
}