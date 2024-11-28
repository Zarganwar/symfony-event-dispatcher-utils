Installation
===

    composer require zarganwar/symfony-event-dispatcher-utils


Usage
===

1. Install this lib
2. Create Subscriber with __invoke method and with typed $event parameter
3. Type of parameter must be a class, interface or union types
4. Declare use Trait from this package
5. Enjoy 😉

Example
===

```php

class CoolSubscriber implements Symfony\Component\EventDispatcher\EventSubscriberInterface
{
    use Zarganwar\SymfonyEventDispatcherUtils\AutoEventSubscriberTrait;

    public function __invoke(CoolEvent $event): void
    {
        // Be Cool
    }

}
```
