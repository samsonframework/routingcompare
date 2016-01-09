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

    public function iterate($identifier, $routeData)
    {
        for ($i = 0; $i < $this->iterationCount; $i++) {
            $url = rawurldecode(parse_url($routeData[2], PHP_URL_PATH));
            $timestamp = microtime(true);
            $routeInfo = $this->collection->dispatch($routeData[0], $url);

            if (isset($routeInfo[1]) && $routeInfo[1] == $identifier) {
                $this->results[$identifier][] = microtime(true) - $timestamp;
            } else {
                $this->results[$identifier][] = 1000;
            }
        }
    }
}
