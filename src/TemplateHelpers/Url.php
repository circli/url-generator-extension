<?php declare(strict_types=1);

namespace Circli\Extensions\UrlGenerator\TemplateHelpers;

use Blueprint\Helper\AbstractHelper;
use Circli\Extensions\UrlGenerator\ActionCollection;

class Url extends AbstractHelper
{
    /** @var ActionCollection */
    private $actionCollection;

    public function __construct(ActionCollection $actionCollection)
    {
        $this->actionCollection = $actionCollection;
    }

    public function getName(): string
    {
        return 'url';
    }

    public function run(array $args)
    {
        if (is_string($args[0]) && class_exists($args[0]) && $this->actionCollection->exists($args[0])) {
            return $this->actionCollection->getUrl($args[0], $args[1] ?? []);
        }
        $currentAction = $this->actionCollection->getCurrentAction();
        if ($currentAction) {
            return $this->actionCollection->getUrl(get_class($currentAction));
        }

        throw new \InvalidArgumentException('First argument must be an action class name');
    }
}