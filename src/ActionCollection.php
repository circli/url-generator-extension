<?php declare(strict_types=1);

namespace Circli\Extensions\UrlGenerator;

use Circli\Extensions\UrlGenerator\Object\Url;
use Polus\Adr\Interfaces\ActionInterface;

class ActionCollection
{
    private $collection = [];
    /** @var ActionInterface|null */
    private $currentAction;

    public function addAction(ActionInterface $action, string $route, string $method)
    {
        if (!isset($this->collection[get_class($action)])) {
            $this->collection[get_class($action)] = [
                'route' => $route,
                'method' => $method,
                'routes' => [
                    $route,
                ],
            ];
        }
        if (in_array($route, $this->collection[get_class($action)]['routes'], true) === false) {
            $this->collection[get_class($action)]['routes'][] = $route;
        }
    }

    public function exists(string $action): bool
    {
        return isset($this->collection[$action]);
    }

    public function getRoute(string $action): ?string
    {
        if (isset($this->collection[$action])) {
            return $this->collection[$action]['route'];
        }
        return null;
    }

    public function getUrl(string $action, array $data = []): Url
    {
        if (!isset($this->collection[$action])) {
            throw new \InvalidArgumentException('No route to action found');
        }
        return Url::fromRoute($this->collection[$action], $data);
    }

    public function setCurrentAction($handler): void
    {
        if ($handler instanceof ActionInterface) {
            $this->currentAction = $handler;
        }
    }

    public function getCurrentAction(): ?ActionInterface
    {
        return $this->currentAction;
    }

    public function getCollection(): array
    {
        return $this->collection;
    }
}
