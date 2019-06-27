<?php declare(strict_types=1);

namespace Circli\Extensions\UrlGenerator;

interface QueryStringBuilder
{
    public function getQuery(): array;
}