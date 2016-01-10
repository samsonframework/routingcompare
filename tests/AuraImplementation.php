<?php
/**
 * Created by PhpStorm.
 * User: onysko
 * Date: 08.01.2016
 * Time: 13:46
 */

namespace samsonframework\routing\tests;



use Aura\Router\Matcher;
use Aura\Router\RouterContainer;
use Zend\Diactoros\ServerRequestFactory;

class AuraImplementation extends RouterImplementation
{
    protected $matcher;

    public $name = 'AuraRouter';

    protected function newRequest($path, array $server = [])
    {
        $server['REQUEST_URI'] = $path;
        $server = array_merge($_SERVER, $server);
        return ServerRequestFactory::fromGlobals($server);
    }

    public function __construct($routes)
    {
        $routerContainer = new RouterContainer();
        $this->collection = $routerContainer->getMap();
        foreach ($routes as $identifier => $route) {
            $this->collection->get($identifier, $route[1]);
        }
        /** @var Matcher $matcher */
        $this->matcher = $routerContainer->getMatcher();
    }

    /**
     * Dispatch route from collection.
     *
     * @param array $routeData Route info
     * @return array Dispatched route info
     */
    public function dispatch($routeData)
    {
        return $this->matcher->match($request);
    }

    public function iterate($identifier, $routeData)
    {
        for ($i = 0; $i < $this->iterationCount; $i++) {
            $request = $this->newRequest($routeData[1]);
            $timestamp = microtime(true);
            $routeInfo = $this->matcher->match($request);
            $this->results[$identifier][] = !empty($routeInfo) ? microtime(true) - $timestamp : 1000;
        }
    }
}
