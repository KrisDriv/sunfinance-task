<?php
declare(strict_types=1);

namespace App\Router\Contracts;

use Symfony\Component\HttpFoundation\Request;

interface RouterInterface
{

    /**
     * Return the handler based on incoming request
     */
    public function resolve(Request $request): ?array;

    /**
     * Store a route and a handling function to be executed when accessed using one of the specified methods.
     *
     * @param string $methods Allowed methods, | delimited
     * @param string $pattern A route pattern such as /about/system
     * @param callable|string $handler The handling function to be executed
     */
    public function match(string $methods, string $pattern, callable|string $handler): void;

    /**
     * Shorthand for a route accessed using any method.
     *
     * @param string $pattern A route pattern such as /about/system
     * @param callable|string $handler The handling function to be executed
     */
    public function all(string $pattern, callable|string $handler): void;

    /**
     * Shorthand for a route accessed using GET.
     *
     * @param string $pattern A route pattern such as /about/system
     * @param callable|string $handler The handling function to be executed
     */
    public function get(string $pattern, callable|string $handler): void;

    /**
     * Shorthand for a route accessed using POST.
     *
     * @param string $pattern A route pattern such as /about/system
     * @param callable|string $handler The handling function to be executed
     */
    public function post(string $pattern, callable|string $handler): void;

    /**
     * Shorthand for a route accessed using PATCH.
     *
     * @param string $pattern A route pattern such as /about/system
     * @param callable|string $handler The handling function to be executed
     */
    public function patch(string $pattern, callable|string $handler): void;

    /**
     * Shorthand for a route accessed using DELETE.
     *
     * @param string $pattern A route pattern such as /about/system
     * @param callable|string $handler The handling function to be executed
     */
    public function delete(string $pattern, callable|string $handler): void;

    /**
     * Shorthand for a route accessed using PUT.
     *
     * @param string $pattern A route pattern such as /about/system
     * @param callable|string $handler The handling function to be executed
     */
    public function put(string $pattern, callable|string $handler): void;

    /**
     * Shorthand for a route accessed using OPTIONS.
     *
     * @param string $pattern A route pattern such as /about/system
     * @param callable|string $handler The handling function to be executed
     */
    public function options(string $pattern, callable|string $handler): void;

    /**
     * Mounts a collection of callbacks onto a base route.
     *
     * @param string $baseRoute The route sub pattern to mount the callbacks on
     * @param callable $handler The callback method
     */
    public function mount(string $baseRoute, callable $handler): void;

    /**
     * Namespace for the controllers
     */
    public function setNamespace(string $namespace): void;

    public function getNamespace(): string;

    public function getAllRoutes(): array;

}