<?php
declare(strict_types=1);

namespace Zarganwar\SymfonyEventDispatcherUtils;

use LogicException;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionUnionType;
use function array_map;
use function class_exists;
use function count;
use function interface_exists;

/**
 * This trait is used to automatically subscribe to events. It requires the __invoke method to be present in the class.
 * The __invoke method must have exactly one parameter with a type or types (type|type).
 * The type of the parameter is the event to which the class will be subscribed.
 * The __invoke method is the method that will be called when the event is dispatched.
 * <code>
 * <?php
 *
 *  use Symfony\Component\EventDispatcher\EventSubscriberInterface;
 *
 *  class ReactsOnSomeKindOfEventSubscriber implements EventSubscriberInterface;
 *  {
 *        use AutoEventSubscriberTrait;
 *
 *        public function __invoke(SomeKindOfEvent|OrEventInterface $event): void
 *        {
 *            process($event);
 *        }
 *  }
 * </code>
 *
 */
trait AutoEventSubscriberTrait
{

	public static function getSubscribedEvents(): array
	{
		$reflection = new ReflectionClass(static::class);
		$methodName = '__invoke';

		if (!$reflection->hasMethod($methodName)) {
			throw new LogicException("Method '{$methodName}' not found.");
		}

		$method = $reflection->getMethod($methodName);
		$methodParams = $method->getParameters();

		if (count($methodParams) !== 1 || !isset($methodParams[0])) {
			throw new LogicException("Method '{$methodName}' must have exactly one parameter.");
		}

		$parameter = $methodParams[0];
		$parameterName = $parameter->getName();
		$parameterType = $parameter->getType();

		if ($parameterType === null) {
			throw new LogicException("Parameter '{$parameterName}' of method '{$methodName}' must have a type.");
		}

		$subscribedEvents = [];

		if ($parameterType instanceof ReflectionUnionType) {
			$typeNames = array_map(
				static fn(ReflectionNamedType $type) => $type->getName(),
				$parameterType->getTypes(),
			);
		} elseif ($parameterType instanceof ReflectionNamedType) {
			$typeNames = [$parameterType->getName()];
		} else {
			throw new LogicException("Unable to read type name of parameter '{$parameterName}'.");
		}

		foreach ($typeNames as $typeName) {
			$subscribedEvents[$typeName] = interface_exists($typeName) || class_exists($typeName)
				? $methodName
				: throw new LogicException("Class '{$typeName}' not found.");
		}

		return $subscribedEvents;
	}

}
