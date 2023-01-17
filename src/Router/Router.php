<?php
declare(strict_types=1);

namespace App\Router;


use App\Router\Contracts\RouterInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class Router.
 */
class Router implements RouterInterface
{

    /**
     * @var array The route patterns and their handling functions
     */
    private array $routes = [];

    /**
     * @var string Current base route, used for (sub)route mounting
     */
    private string $baseRoute = '';

    /**
     * @var string Default Controllers Namespace
     */
    private string $namespace = '';

    public function getAllRoutes(): array
    {
        return $this->routes;
    }

    public function match(string $methods, string $pattern, callable|string $handler): void
    {
        $pattern = $this->baseRoute . '/' . trim($pattern, '/');
        $pattern = $this->baseRoute ? rtrim($pattern, '/') : $pattern;

        foreach (explode('|', $methods) as $method) {
            $this->routes[$method][] = array(
                'pattern' => $pattern,
                'fn' => $handler,
            );
        }
    }

    public function all(string $pattern, callable|string $handler): void
    {
        $this->match('GET|POST|PUT|DELETE|OPTIONS|PATCH|HEAD', $pattern, $handler);
    }

    public function get(string $pattern, callable|string $handler): void
    {
        $this->match('GET', $pattern, $handler);
    }

    public function post(string $pattern, callable|string $handler): void
    {
        $this->match('POST', $pattern, $handler);
    }


    public function patch(string $pattern, callable|string $handler): void
    {
        $this->match('PATCH', $pattern, $handler);
    }

    public function delete(string $pattern, callable|string $handler): void
    {
        $this->match('DELETE', $pattern, $handler);
    }

    public function put(string $pattern, callable|string $handler): void
    {
        $this->match('PUT', $pattern, $handler);
    }

    public function options(string $pattern, callable|string $handler): void
    {
        $this->match('OPTIONS', $pattern, $handler);
    }

    public function mount(string $baseRoute, callable $handler): void
    {
        // Track current base route
        $curBaseRoute = $this->baseRoute;

        // Build new base route string
        $this->baseRoute .= $baseRoute;

        // Call the callable
        call_user_func($handler);

        // Restore original base route
        $this->baseRoute = $curBaseRoute;
    }

    public function setNamespace(string $namespace): void
    {
        $this->namespace = $namespace;
    }

    public function getNamespace(): string
    {
        return $this->namespace;
    }


    private function patternMatches($pattern, $uri, &$matches): bool
    {
        // Replace all curly braces matches {} into word patterns (like Laravel)
        $pattern = preg_replace('/\/{(.*?)}/', '/(.*?)', $pattern);

        // we may have a match!
        return boolval(preg_match_all('#^' . $pattern . '$#', $uri, $matches, PREG_OFFSET_CAPTURE));
    }

    public function extractParameters(array $matches): array
    {
        // Rework matches to only contain the matches, not the orig string
        $matches = array_slice($matches, 1);

        // Extract the matched URL parameters (and only the parameters)
        return array_map(function ($match, $index) use ($matches) {

            // We have a following parameter: take the substring from the current param position until the next one's position
            if (isset($matches[$index + 1]) && isset($matches[$index + 1][0]) && is_array($matches[$index + 1][0])) {
                if ($matches[$index + 1][0][1] > -1) {
                    return trim(substr($match[0][0], 0, $matches[$index + 1][0][1] - $match[0][1]), '/');
                }
            }

            return isset($match[0][0]) && $match[0][1] != -1 ? trim($match[0][0], '/') : null;
        }, $matches, array_keys($matches));
    }

    public function resolve(Request $request): ?array
    {
        // Define which method we need to handle
        $requestedMethod = $request->getMethod();
        $uri = $request->getPathInfo();

        if (!isset($this->routes[$requestedMethod])) {
            return null;
        }

        $methodRoutes = $this->routes[$requestedMethod];

        // Loop all routes
        foreach ($methodRoutes as $route) {
            if ($this->patternMatches($route['pattern'], $uri, $matches)) {
                $matchedRoute = $route;
                break;
            }
        }

        if (!isset($matchedRoute) || !isset($matches)) {
            return null;
        }

        return [
            'route' => $matchedRoute,
            'parameters' => $this->extractParameters($matches),
        ];
    }


}
