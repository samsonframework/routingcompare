<?php
/**
 * Created by PhpStorm.
 * User: onysko
 * Date: 08.01.2016
 * Time: 13:46
 */

namespace samsonframework\routing\tests;



class FastRouteImplementation extends RouterImplementation
{
    protected $matcher;

    public $name = 'FastRoute';

    public function __construct($routes)
    {
        $this->collection = \FastRoute\simpleDispatcher(function(\FastRoute\RouteCollector $r) use ($routes) {
            foreach ($routes as $identifier => $routeData) {
                $r->addRoute($routeData[0], $routeData[1], $identifier);
            }
        }, ['cacheFile' => __DIR__ . '/route.cache']);
    }

    /**
     * Dispatch route from collection.
     *
     * @param array $routeData Route info
     * @return array Dispatched route info
     */
    public function dispatch($routeData)
    {
        $routeInfo = $this->collection->dispatch($routeData[0], $routeData[2]);
        if (isset($routeInfo[1])) {
            return $routeInfo;
        }

        return null;
    }
}
