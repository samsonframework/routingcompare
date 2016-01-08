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

class SymfonyImplementation extends RouterImplementation
{
    protected $matcher;

    public $name = 'Symfony';

    public function __construct($routes)
    {
        $this->collection = new RouteCollection();
        foreach ($routes as $identifier => $route) {
            $this->collection->add($identifier, new Route($route[1]));
        }
        $context = new RequestContext();

        $this->matcher = new UrlMatcher($this->collection, $context);
    }

    public function dispatch($routeData)
    {
        return $this->matcher->match($routeData[1]);
    }
}
