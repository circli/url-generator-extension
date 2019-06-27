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
        $this->collection[get_class($action)] = [
            'route' => $route,
            'method' => $method,
        ];
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
        $route = $this->getRoute($action);
        if (!$route) {
            throw new \InvalidArgumentException('No route to action found');
        }
        return Url::fromRoute($route, $data);
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
}
