<?php
/**
 * Created by PhpStorm.
 * User: onysko
 * Date: 08.01.2016
 * Time: 13:46
 */

namespace samsonframework\routing\tests;


class AltoImplementation extends RouterImplementation
{
    public $name = 'AltoRouter';

    public function baseCallback()
    {
        return __FUNCTION__;
    }

    public function __construct($routes)
    {
        $this->collection = new \AltoRouter();
        foreach ($routes as $identifier => $route) {
            $this->collection->map($route[0], $route[1], $this->baseCallback(), $identifier);
        }
    }

    public function dispatch($routeData)
    {
        return $this->collection->match($routeData[1]);
    }
}
