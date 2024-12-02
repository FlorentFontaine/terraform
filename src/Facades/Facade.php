<?php

namespace Facades;

/**
 * The Facade class is an abstract class that provides a simplified interface for accessing services within a container.
 * Child classes should extend this class and implement the definition() method to specify the service that the Facade should be linked with.
 *
 * Example usage:
 *
 * class MyFacade extends Facade
 * {
 *     public static function definition(): string
 *     {
 *         return 'myService';
 *     }
 * }
 *
 * // Calling a method on the facade will resolve the service from the container and call the corresponding method on the service.
 * MyFacade::someMethod($arg1, $arg2);
 */
abstract class Facade
{
    /**
     * Return the definition of what the facade should be linked with.
     * This method is abstract and static, so it needs to be implemented in child classes.
     *
     * @return string
     */
    abstract public static function definition(): string;

    /**
     * Handle calling static methods statically.
     *
     * This method is magic and is automatically called when invoking a static method that doesn't exist in the class.
     * The method resolves the service from the container based on the definition provided by the child class,
     * and calls the requested method on the resolved service instance with the provided arguments.
     *
     * @param string $method The name of the method being called statically.
     * @param array $args The arguments passed to the method.
     * @return mixed The result of the called method.
     *
     * @throws \Exception If the definition provided by the child class is not a valid service identifier.
     */
    public static function __callStatic(string $method, array $args)
    {
            $container = require __DIR__ . "/../Classes/Container/Container.php";
            return  $container->get(static::definition())->{$method}(...$args);
    }
}
