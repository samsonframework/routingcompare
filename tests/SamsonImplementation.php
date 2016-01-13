<?php
/**
 * Created by PhpStorm.
 * User: onysko
 * Date: 08.01.2016
 * Time: 13:46
 */

namespace samsonframework\routing\tests;


use samsonframework\routing\generator\Structure;
use samsonframework\routing\Route;
use samsonframework\routing\RouteCollection;
use samsonphp\generator\Generator;

class SamsonImplementation extends RouterImplementation
{
    protected $routerLogicFunction;

    public $name = 'Samson';

    public function baseCallback()
    {
        return __FUNCTION__;
    }

    public function __construct($routes)
    {
        // Create samsonframework\routing routes collection
        $this->collection = new RouteCollection();
        foreach ($routes as $identifier => $routeData) {
            $this->collection->add(new Route($routeData[1], array($this, 'baseCallback'), $identifier, $routeData[0]));
        }

        // Generate routing logic
        $generator = new Structure($this->collection, new Generator());
        $this->routerLogicFunction = '__router' . rand(0, 1000);
        $routerLogic = $generator->generate($this->routerLogicFunction);

        // Create real file for debugging
        file_put_contents(__DIR__ . '/testLogic.php', '<?php ' . "\n" . $routerLogic);
        require(__DIR__ . '/testLogic.php');
    }

    public function beforeDispatch(&$routeData)
    {
        $routeData[2] = rtrim(strtok(ltrim($routeData[2], '/'), '?'), '/');
    }

    public function dispatch($routeData)
    {
        return call_user_func_array($this->routerLogicFunction, array($routeData[2], $routeData[0]));
    }
}
