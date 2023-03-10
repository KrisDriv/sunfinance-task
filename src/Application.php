<?php
declare(strict_types=1);

namespace App;

use App\Contracts\Error\ExceptionHandler;
use App\Contracts\Http\RequestHandler;
use App\Contracts\Http\ResponsePresenter;
use App\Router\Contracts\RouterInterface;
use DI\Container;
use DI\ContainerBuilder;
use DI\DependencyException;
use DI\NotFoundException;
use Doctrine\DBAL\Connection;
use Exception;
use HaydenPierce\ClassFinder\ClassFinder;
use Psr\Log\LoggerInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionMethod;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use function DI\create;

class Application implements RequestHandler, ResponsePresenter
{

    private Container $container;

    private RouterInterface $router;

    private Connection $connection;

    private LoggerInterface $logger;

    private ExceptionHandler&\App\Contracts\Error\ErrorHandler $exceptionHandler;

    private array $discoverNamespaces = [];

    private string $controllerNamespace = 'App\\Controllers';

    private string $listenerNamespace = 'App\\Listeners';

    /**
     * @throws Exception
     */
    public function __construct()
    {
        $this->container = $this->buildContainer();

        $this->router = $this->container->get(RouterInterface::class);
        $this->connection = $this->container->get(Connection::class);
        $this->logger = $this->container->get(LoggerInterface::class);

        $this->boot();
    }

    private function boot()
    {
        try {
            $this->router->registerRoutesFromControllerAttributes(
                ClassFinder::getClassesInNamespace($this->controllerNamespace, ClassFinder::RECURSIVE_MODE)
            );
        } catch (Exception $e) {
            $this->logger->error('Routes from controller attributes failed to register', ['exception' => $e]);
        }

        try {
            $this->registerListeners();
        } catch (Exception $e) {
            $this->logger->error('Failed to register event listeners', ['exception' => $e]);
        }

        if (IS_DEV) {
            try {
                $this->exceptionHandler = $this->container->get(ExceptionHandler::class);
            } catch (DependencyException|NotFoundException $e) {
                $this->logger->error('Exception thrown when attaching Exception&Error Handler', ['exception' => $e]);
            }
        }
    }

    public function getConnection(): Connection
    {
        return $this->connection;
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

        // Default request and response flow
        $builder->addDefinitions([
            RequestHandler::class => fn(): RequestHandler => $this,
            ResponsePresenter::class => fn(): ResponsePresenter => $this
        ]);

        $builder->addDefinitions(
            $this->getContainerDefinitionsFromNamespaces(
                $this->discoverNamespaces
            )
        );

        $builder->addDefinitions(ROOT . 'config/container.php');

        return $builder->build();
    }

    /**
     * @throws ReflectionException
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function handle(Request $request): Response
    {
        $match = $this->router->resolve($request);

        // TODO:
        if (null === $match) {
            return new Response('Page not found', Response::HTTP_NOT_FOUND);
        }

        $route = $match['route'];
        $queryParameters = $match['parameters'];
        $handler = $route['fn'];

        // Handle anonymous functions
        if (is_callable($handler)) {
            $function = new ReflectionFunction($handler);

            return $this->normalizeHandlerResponse(
                call_user_func_array(
                    $handler,
                    $this->resolveHandlerParameters($function, $queryParameters)
                )
            );
        }

        // Handle controller calls
        if (isset($handler) && is_string($handler) && str_contains($handler, '@')) {
            list($controllerShortName, $method) = explode('@', $handler);

            $fullyQualifiedControllerClass = $this->router->getNamespace() . '\\' . $controllerShortName;

            try {
                $controller = $this->getContainer()->get($fullyQualifiedControllerClass);
            } catch (NotFoundException $e) {
                if (IS_DEV) {
                    return $this->normalizeHandlerResponse($e->getMessage(), 404, 'Controller not found');
                } else {
                    return $this->normalizeHandlerResponse('Page not found.', 404);
                }
            }

            $reflectionClass = new ReflectionClass($fullyQualifiedControllerClass);

            // Allow invocable controllers
            $function = $reflectionClass->getMethod($method ?? '__invoke');

            return $this->normalizeHandlerResponse(
                call_user_func_array(
                    [$controller, $function->name],
                    $this->resolveHandlerParameters($function, $queryParameters)
                )
            );
        }

        return (new Response())
            ->setContent('Page not found')
            ->setStatusCode(Response::HTTP_NOT_FOUND);
    }

    /**
     * @throws DependencyException
     * @throws NotFoundException|ReflectionException
     */
    private function resolveHandlerParameters(ReflectionMethod|ReflectionFunction $function, array $queryParameters): array
    {
        $controllerMethodParameters = $function->getParameters();
        $validParameters = [];

        $scalarParameterIndex = 0;
        foreach ($controllerMethodParameters as $methodParameterIndex => $parameter) {
            $type = $parameter->getType()->getName();
            $parameterName = $parameter->getName();

            // If parameter is built-in, meaning we have encountered scalar value
            // controller might be trying to achieve one of two things: pull parameter from container
            // or value from URL
            if ($parameter->getType()->isBuiltin()) {
                // Scalar values (TODO: Might be pulled from container, if no default provided)
                // or if default provided, ignore container exception ...
                $scalarValue = $queryParameters[$scalarParameterIndex];

                // Prioritise URL parameter
                if (!is_null($scalarValue)) {
                    settype($scalarValue, $type);

                    $validParameters[$methodParameterIndex] = $queryParameters[$scalarParameterIndex]
                        ? $scalarValue
                        : $parameter->getDefaultValue();
                } else {
                    $validParameters[$methodParameterIndex] = $this->getContainer()->get($parameterName);
                }
            } else {
                // Otherwise, we request the Object from container
                $fromContainer = $this->getContainer()->get($type);

                $validParameters[$methodParameterIndex] = $fromContainer;
            }
        }

        return $validParameters;
    }

    public function present(Response $response): void
    {
        $response->send();
    }

    private function normalizeHandlerResponse(mixed $raw, ?int $status = null, ?string $message = null): Response
    {
        if ($raw instanceof Response) {
            return $raw;
        }

        $response = new Response();
        $response->setContent(is_array($raw) ? json_encode($raw, JSON_PRETTY_PRINT) : (string)$raw);

        return $response;
    }

    private function getContainerDefinitionsFromNamespaces(array $namespaces): array
    {
        $definitions = [];
        foreach ($namespaces as $namespace) {
            try {
                $classes = ClassFinder::getClassesInNamespace($namespace, ClassFinder::RECURSIVE_MODE);

                // Register classes in the container
                foreach ($classes as $class) {
                    $definitions[$class] = create($class);
                }
            } catch (Exception) {
                // TODO: Log
                continue;
            }
        }

        return $definitions;
    }

    /**
     * @throws DependencyException
     * @throws NotFoundException
     * @throws Exception
     */
    private function registerListeners(): void
    {
        /** @var EventDispatcher $eventDispatcher */
        $eventDispatcher = $this->getContainer()->get(EventDispatcher::class);

        foreach (ClassFinder::getClassesInNamespace($this->listenerNamespace) as $listenerClass) {
            $listener = $this->getContainer()->get($listenerClass);

            $handlers = $listener->getHandlers();

            foreach ($handlers as $event => $handler) {
                $priority = 0;
                if (is_array($handler)) {
                    [$handler, $priority] = $handler;
                }

                $callable = [$listener, $handler];

                if (!is_callable($callable)) {
                    $this->logger->warning("Listener $listenerClass::$handler for event '$event' was not registered. Method not found.");
                    continue;
                }

                $eventDispatcher->addListener($event, [$listener, $handler], $priority);
            }
        }
    }

}