<?php declare(strict_types=1);

namespace Circli\Extensions\UrlGenerator\Object;

final class UrlRoute
{
    public const SIMPLE = 'simple';
    public const COMPLEX = 'complex';
    /** @var string */
    private $type;
    /** @var string */
    private $route;
    /** @var array */
    private $params;

    public static function fromString(string $route): self
    {
        if (strpos($route, '{')) {
            $rs = preg_match_all('/(\{(\w+)(\:([^\/]*))?\})/', $route, $matches, PREG_SET_ORDER);
            if ($rs) {
                $params = [];
                foreach ($matches as $match) {
                    $name = $match[2];
                    $rule = $match[4] ?? null;
                    $value = null;
                    $params[$name] = [
                        'value' => $value,
                        'replace' => $match[0],
                        'rule' => $rule
                    ];
                }
                return new self(self::COMPLEX, $route, $params);
            }
        }

        return new self(self::SIMPLE, $route);
    }

    private function __construct(string $type, string $route, array $params = [])
    {
        $this->type = $type;
        $this->route = $route;
        $this->params = $params;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getRoute(): string
    {
        return $this->route;
    }

    public function getParams(): array
    {
        return $this->params;
    }
}
