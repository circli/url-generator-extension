<?php declare(strict_types=1);

namespace Circli\Extensions\UrlGenerator;

final class SimpleQueryStringBuilder implements QueryStringBuilder
{
    /** @var array */
    private $tokens;

    public static function fromArray(array $tokens): self
    {
        return new self($tokens);
    }

    public static function fromString(string $query): self
    {
        parse_str($query, $parsedQuery);
        return new self($parsedQuery);
    }

    private function __construct(array $tokens)
    {
        $this->tokens = $tokens;
    }

    public function getQuery(): array
    {
        return $this->tokens;
    }
}