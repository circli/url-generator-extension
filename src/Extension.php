<?php declare(strict_types=1);

namespace Circli\Extensions\UrlGenerator;

use Circli\Contracts\ExtensionInterface;
use Circli\Contracts\PathContainer;
use Circli\EventDispatcher\ListenerProvider\DefaultProvider;
use Circli\Extensions\UrlGenerator\TemplateHelpers\Url;
use Circli\WebCore\Events\PostRouteDispatch;
use Circli\WebCore\Events\PreRegisterRoute;
use Fig\EventDispatcher\AggregateProvider;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use function DI\autowire;
use function DI\decorate;

class Extension implements ExtensionInterface
{
    public function __construct(PathContainer $paths)
    {
    }

    public function configure(): array
    {
        return [
            ActionCollection::class => autowire(ActionCollection::class),
            Url::class => autowire(Url::class),
            AggregateProvider::class => decorate(static function ($previous, ContainerInterface $container) {
                if (!$previous instanceof AggregateProvider) {
                    $previous = new AggregateProvider();
                }
                $defaultProvider = new DefaultProvider();
                $collection = $container->get(ActionCollection::class);
                $defaultProvider->listen(PreRegisterRoute::class, static function (PreRegisterRoute $event) use ($collection) {
                    $collection->addAction($event->getAction(), $event->getRoute(), $event->getMethod());
                });
                $defaultProvider->listen(PostRouteDispatch::class, static function (PostRouteDispatch $event) use ($collection) {
                    $collection->setCurrentAction($event->getRoute()->getHandler());
                });
                $previous->addProvider($defaultProvider);
                return $previous;
            }),
        ];
    }
}
