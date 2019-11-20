<?php declare(strict_types=1);

namespace Circli\Extensions\UrlGenerator\Object;

use Circli\Extensions\UrlGenerator\QueryStringBuilder;
use Circli\Extensions\UrlGenerator\SimpleQueryStringBuilder;
use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;

final class Url
{
    /** @var UrlRoute */
    private $bestMatch;
    /** @var UrlRoute[] */
    private $routes = [];
    /** @var array */
    private $params;
    /** @var QueryStringBuilder */
    private $query;

    public static function fromRoute(array $routeData, array $replaceParams = []): self
    {
        $routes = [];
        $params = [];
        foreach ($routeData['routes'] as $stringRoute) {
            $route = UrlRoute::fromString($stringRoute);
            $params += $route->getParams();
            $routes[] = $route;
        }
        $self = new self($routes, $params);
        foreach ($replaceParams as $name => $param) {
            $self = $self->withParam($name, $param);
        }
        return $self;
    }

    public static function fromRequest(ServerRequestInterface $request): self
    {
        return new self([
            UrlRoute::fromString($request->getUri()->getPath())
        ], []);
    }

    private function __construct(array $routes, array $params)
    {
        $this->routes = $routes;
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

            if ($param['value'] === null) {
                $this->bestMatch = null;
            }

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
        try {
            $params = array_filter($this->params, function($param) {
                return $param['value'] !== null;
            });

            if (!$this->bestMatch) {
                $paramCount = count($params);
                foreach ($this->routes as $route) {
                    $routeParamCount = count($route->getParams());
                    if ($paramCount === 0 && $routeParamCount === 0) {
                        $this->bestMatch = $route;
                        break;
                    }

                    if ($paramCount === $routeParamCount) {
                        //todo check param names
                        $this->bestMatch = $route;
                        break;
                    }

                    $this->bestMatch = $route;
                }
            }

            $url = $this->bestMatch->getRoute();
            foreach ($this->bestMatch->getParams() as $name => $routeParam) {
                $valueParam = $this->params[$name];
                $url = str_replace($routeParam['replace'], $valueParam['value'] ?? $name, $url);
            }

            if ($this->query instanceof QueryStringBuilder) {
                $url .= '?' . http_build_query($this->query->getQuery());
            }

            return $url;
        }
        catch (\Throwable $e) {
            return $e->getMessage();
        }
    }

    public function __toString()
    {
        return $this->toString();
    }
}
