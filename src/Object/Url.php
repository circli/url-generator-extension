<?php declare(strict_types=1);

namespace Circli\Extensions\UrlGenerator\Object;

use Circli\Extensions\UrlGenerator\QueryStringBuilder;
use Circli\Extensions\UrlGenerator\SimpleQueryStringBuilder;
use InvalidArgumentException;

final class Url
{
    /** @var string */
    private $route;
    /** @var array */
    private $params;
    /** @var QueryStringBuilder */
    private $query;

    public static function fromRoute(string $route, array $replaceParams = [])
    {
        $params = [];
        if (strpos($route, '{')) {
            $rs = preg_match_all('/(\{(\w+)(\:([^\/]*))?\})/', $route, $matches, PREG_SET_ORDER);
            if ($rs) {
                foreach ($matches as $match) {
                    $name = $match[2];
                    $rule = $match[4] ?? null;
                    $value = null;
                    if (isset($replaceParams[$name])) {
                        $value = $replaceParams[$name];
                        if (is_object($value)) {
                            if (method_exists($value, 'toString')) {
                                $value = $value->toString();
                            }
                            elseif (method_exists($value, '__toString')) {
                                $value = (string) $value;
                            }
                        }
                        if ($rule && !preg_match('/' . $rule . '/', $value)) {
                            throw new InvalidArgumentException('Value for "' . $name . '" is not of valid type.');
                        }
                    }
                    $params[$match['2']] = [
                        'value' => $value,
                        'replace' => $match[0],
                        'rule' => $rule
                    ];
                }
            }
        }

        return new self($route, $params);
    }

    private function __construct(string $route, array $params)
    {
        $this->route = $route;
        $this->params = $params;
    }

    public function withParam(string $paramName, $value): self
    {
        if (isset($this->params[$paramName])) {
            $param = $this->params[$paramName];
            if (is_object($value)) {
                if (method_exists($value, 'toString')) {
                    $value = $value->toString();
                }
                elseif (method_exists($value, '__toString')) {
                    $value = (string) $value;
                }
            }
            if ($param['rule'] && !preg_match('/' . $param['rule'] . '/', $value)) {
                throw new InvalidArgumentException('Value for "' . $paramName . '" is not of valid type.');
            }

            $self = clone $this;
            $param['value'] = $value;
            $self->params[$paramName] = $param;
            return $self;
        }

        return $this;
    }

    public function withQuery($query): self
    {
        if (!$query instanceof QueryStringBuilder) {
            if (is_string($query)) {
                $query = SimpleQueryStringBuilder::fromString($query);
            }
            elseif (is_array($query)) {
                $query = SimpleQueryStringBuilder::fromArray($query);
            }
            else {
                throw new InvalidArgumentException('Must be string, array or QueryStringBuilder');
            }
        }
        $self = clone $this;
        $self->query = $query;
        return $self;
    }

    public function toString(): string
    {
        $url = $this->route;
        foreach ($this->params as $name => $param) {
            $url = str_replace($param['replace'], $param['value'] ?? $name, $url);
        }

        if ($this->query instanceof QueryStringBuilder) {
            $url .= '?' . http_build_query($this->query->getQuery());
        }

        return $url;
    }

    public function __toString()
    {
        return $this->toString();
    }
}
