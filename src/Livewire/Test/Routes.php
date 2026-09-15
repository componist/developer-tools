<?php

namespace Componist\DeveloperTools\Livewire\Test;

use Componist\DeveloperTools\Infrastructure\AuthorizesDeveloperTools;
use Illuminate\Support\Facades\Route;
use Livewire\Component;

class Routes extends Component
{
    use AuthorizesDeveloperTools;

    public array $routes = [];

    public function mount()
    {
        $this->loadRoutes();
    }

    public function loadRoutes()
    {
        $this->routes = [];
        $routeCollection = Route::getRoutes()->get();

        foreach ($routeCollection as $route) {
            // Check if route has dynamic parameters (e.g., {id}, {slug})
            $hasDynamicParams = preg_match('/\{[^}]+\}/', $route->uri);
            
            $temp = [
                'method' => $route->methods[0],
                'url' => $route->uri,
                'fullUrl' => url($route->uri),
                'dynamicUrl' => $hasDynamicParams,
                'test' => false,
                'status' => null,
                'tested' => false,
                'error' => null,
            ];

            // Mark only GET routes without dynamic parameters as testable
            if ($route->methods[0] == 'GET' && !$hasDynamicParams) {
                $temp['test'] = true;
            }

            $this->routes[] = $temp;
        }
    }

    public function updateRouteStatus(int $index, ?int $status, ?string $error = null, bool $pending = false): void
    {
        if (isset($this->routes[$index])) {
            if ($pending) {
                $this->routes[$index]['tested'] = false;
                $this->routes[$index]['status'] = 'pending';
                $this->routes[$index]['error'] = null;
            } else {
                $this->routes[$index]['tested'] = true;
                $this->routes[$index]['status'] = $status ?? 'error';
                $this->routes[$index]['error'] = $error;
            }
        }
    }

    public function getGroupedRoutesProperty(): array
    {
        $grouped = [];
        
        // Method priority for sorting (lower number = higher priority)
        $methodPriority = [
            'GET' => 1,
            'POST' => 2,
            'PUT' => 3,
            'PATCH' => 4,
            'DELETE' => 5,
            'HEAD' => 6,
            'OPTIONS' => 7,
            'TRACE' => 8,
            'CONNECT' => 9,
        ];
        
        foreach ($this->routes as $index => $route) {
            $url = trim($route['url'], '/');
            
            // Handle root route
            if (empty($url)) {
                $firstLevel = 'root';
            } else {
                $parts = explode('/', $url);
                // Get first level (e.g., 'users', 'dashboard', 'api')
                $firstLevel = ($parts[0] !== '') ? $parts[0] : 'root';
            }
            
            if (!isset($grouped[$firstLevel])) {
                $grouped[$firstLevel] = [];
            }
            
            // Store the original index for testing
            $route['originalIndex'] = $index;
            $grouped[$firstLevel][] = $route;
        }
        
        // Sort routes within each group by method, then by URL
        foreach ($grouped as $groupName => $routes) {
            usort($grouped[$groupName], function ($a, $b) use ($methodPriority) {
                $priorityA = $methodPriority[$a['method']] ?? 99;
                $priorityB = $methodPriority[$b['method']] ?? 99;
                
                // First sort by method priority
                if ($priorityA !== $priorityB) {
                    return $priorityA <=> $priorityB;
                }
                
                // If same method, sort by URL alphabetically
                return strcmp($a['url'], $b['url']);
            });
        }
        
        // Sort groups alphabetically, but put 'root' first
        $rootRoutes = $grouped['root'] ?? [];
        unset($grouped['root']);
        ksort($grouped);
        
        if (!empty($rootRoutes)) {
            $grouped = ['root' => $rootRoutes] + $grouped;
        }
        
        return $grouped;
    }

    public function render()
    {
        return view('developerTools::livewire.test.routes')->layout('developerTools::layouts.package');
    }
}