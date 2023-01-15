<?php
declare(strict_types=1);

namespace App;

use App\Http\Contracts\RequestHandler;
use App\Http\Contracts\ResponsePresenter;
use App\Router\Contracts\RouterInterface;
use DI\Container;
use DI\ContainerBuilder;
use DI\DependencyException;
use DI\NotFoundException;
use Doctrine\DBAL\Connection;
use Exception;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Application implements RequestHandler, ResponsePresenter
{

    private Container $container;
    private RouterInterface $router;
    private Connection $connection;

    /**
     * @throws Exception
     */
    public function __construct()
    {
        $this->container = $this->buildContainer();

        $this->router = $this->container->get(RouterInterface::class);
        $this->connection = $this->container->get(Connection::class);
    }

    public function getContainer(): Container
    {
        return $this->container;
    }

    public function getRouter(): RouterInterface
    {
        return $this->router;
    }

    /**
     * @throws Exception
     */
    private function buildContainer(): Container
    {
        $builder = new ContainerBuilder();

        $builder->writeProxiesToFile(IS_PRODUCTION, ROOT . 'tmp');

        $builder->addDefinitions(ROOT . 'config/container.php');

        return $builder->build();
    }

    public function handle(Request $request): Response
    {
        $match = $this->router->resolve($request);

        if (null === $match) {
            return new Response('Page not found', Response::HTTP_NOT_FOUND);
        }

        $route = $match['route'];
        $routeParameters = $match['parameters'];
        $handler = $route['fn'];

        // Handle anonymous functions
        if (is_callable($handler)) {
            return $this->normalizeHandlerResponse(call_user_func($handler, $request));
        }

        // Handle controller calls
        if (isset($handler) && is_string($handler) && str_contains($handler, '@')) {
            list($controllerShortName, $method) = explode('@', $handler);

            $fullyQualifiedControllerClass = $this->router->getNamespace() . '\\' . $controllerShortName;
            try {
                $controller = $this->getContainer()->get($fullyQualifiedControllerClass);

                $reflectionClass = new ReflectionClass($fullyQualifiedControllerClass);
                $method = $reflectionClass->getMethod($method);
                $parameters = $method->getParameters();

                $scalarParameterIndex = 0;
                foreach ($parameters as $methodParameterIndex => $parameter) {
                    $type = $parameter->getType()->getName();
                    $parameterName = $parameter->getName();


                    // If parameter is built-in, meaning we have encountered scalar value
                    // controller might be trying to achieve one of two things: pull parameter from container
                    // or value from URL
                    if ($parameter->getType()->isBuiltin()) {
                        // Scalar values (TODO: Might be pulled from container, if no default provided)
                        // or if default provided, ignore container exception ...
                        $scalarValue = $routeParameters[$scalarParameterIndex];

                        // Prioritise URL parameter
                        if (!is_null($scalarValue)) {
                            settype($scalarValue, $type);

                            $parameters[$methodParameterIndex] = $routeParameters[$scalarParameterIndex]
                                ? $scalarValue
                                : $parameter->getDefaultValue();
                        } else {
                            $parameters[$methodParameterIndex] = $this->getContainer()->get($parameterName);
                        }
                    } else {
                        // Otherwise, we request the Object from container
                        $fromContainer = $this->getContainer()->get($type);

                        $parameters[$methodParameterIndex] = $fromContainer;
                    }
                }
            } catch (ReflectionException) {
                // TODO: Log
            } catch (DependencyException) {
                // TODO: Log
            } catch (NotFoundException) {
                // TODO: Log
            }

            // TODO: Missing error handler

            return $this->normalizeHandlerResponse(call_user_func_array([$controller, $method->name], $parameters));
        }

        return (new Response())
            ->setContent('Page not found')
            ->setStatusCode(Response::HTTP_NOT_FOUND);
    }

    public function present(Response $response): void
    {
        $response->send();
    }

    public function getConnection(): Connection
    {
        return $this->connection;
    }

    private function normalizeHandlerResponse(mixed $raw): Response
    {
        if ($raw instanceof Response) {
            return $raw;
        }

        $response = new Response();
        $response->setContent(is_array($raw) ? json_encode($raw, JSON_PRETTY_PRINT) : (string)$raw);

        return $response;
    }

}