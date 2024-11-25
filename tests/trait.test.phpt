<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Tester\Assert;
use Zarganwar\SymfonyEventDispatcherUtils\AutoEventSubscriberTrait;

class HappyEvent
{
	public function __construct(public readonly string $message) {}

}

class FluffyEvent
{
	public function __construct(public readonly string $message) {}

}

interface WowEventInterface
{
}

class Subscriber
{
	use AutoEventSubscriberTrait;
}


// ------------------------------------------------------------------
// Right usage tests ----------------------------------------------
Assert::equal((
new class extends Subscriber {
	public function __invoke(HappyEvent $event): void {}

})::getSubscribedEvents(), [HappyEvent::class => '__invoke'], 'Single type hint.');


Assert::equal((
new class extends Subscriber {
	public function __invoke(HappyEvent|FluffyEvent $event): void {}

})::getSubscribedEvents(), [
	HappyEvent::class => '__invoke',
	FluffyEvent::class => '__invoke',
], 'Multiple type hints.');


Assert::equal((
new class extends Subscriber {
	public function __invoke(WowEventInterface $event): void {}

})::getSubscribedEvents(), [WowEventInterface::class => '__invoke'], 'Interface as type hint.');



// ------------------------------------------------------------------
// Wrong usage tests ----------------------------------------------
Assert::exception(fn() => (
new class extends Subscriber {
	public function __cherokee(WowEventInterface $event): void {}

})::getSubscribedEvents(), LogicException::class, "Method '__invoke' not found.");


Assert::exception(fn() => (
new class extends Subscriber {
	public function __invoke(): void {}

})::getSubscribedEvents(), LogicException::class, "Method '__invoke' must have exactly one parameter.");


Assert::exception(fn() => (
new class extends Subscriber {
	public function __invoke(WowEventInterface $event, int $whatIsThis): void {}

})::getSubscribedEvents(), LogicException::class, "Method '__invoke' must have exactly one parameter.");


Assert::exception(fn() => (
new class extends Subscriber {
	public function __invoke(string $eventName): void {}

})::getSubscribedEvents(), LogicException::class, "Class 'string' not found.");


Assert::exception(fn() => (
new class extends Subscriber {
	public function __invoke($whoami): void {}

})::getSubscribedEvents(), LogicException::class, "Parameter 'whoami' of method '__invoke' must have a type.");