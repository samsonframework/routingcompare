<?php
/**
 * Created by PhpStorm.
 * User: onysko
 * Date: 08.01.2016
 * Time: 13:46
 */
namespace samsonframework\routing\tests;

use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Router;
use Symfony\Component\Config\Loader\Loader;

class ExtraLoader extends Loader
{
    private $loaded = false;
    private $routes;
    public $collection;

    public function __construct($routes)
    {
        $this->routes = $routes;
    }

    public function load($resource, $type = null)
    {
        $this->collection = new RouteCollection();
        foreach ($this->routes as $identifier => $route) {
            $this->collection->add($identifier, new Route($route[1]));
        }

        return $this->collection;
    }

    public function supports($resource, $type = null)
    {
        return 'extra' === $type;
    }
}

class SymfonyImplementation extends RouterImplementation
{
    protected $router;

    public $name = 'Symfony';

    public function __construct($routes)
    {
        $this->router = new Router(
            new ExtraLoader($routes),
            'routes.yml',
            array('cache_dir' => __DIR__.'/cache'),
            new RequestContext()
        );
    }

    public function dispatch($routeData)
    {
        return $this->router->match($routeData[1]);
    }
}
