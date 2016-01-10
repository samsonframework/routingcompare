<?php
/**
 * Created by PhpStorm.
 * User: onysko
 * Date: 08.01.2016
 * Time: 13:32
 */
namespace samsonframework\routing\tests;


abstract class RouterImplementation
{
    /** @var int Count of iterations for each route */
    public $iterationCount = 10;

    /** @var array Results for each route */
    public $results = [];

    /** @var array Average results for each route */
    public $averageResults = [];

    /** @var string Test name */
    public $name = 'Test';

    /** @var array Routes collection */
    public $collection;


    /**
     * Router dispatching testing iteration.
     *
     * @param string $identifier Route identifier
     * @param string $routeData Route path to dispatch
     */
    public function iterate($identifier, $routeData)
    {
        for ($i = 0; $i < $this->iterationCount; $i++) {
            $timestamp = microtime(true);
            $routeInfo = $this->dispatch($routeData);
            $this->results[$identifier][] = !empty($routeInfo) ? microtime(true) - $timestamp : 1000;
        }
    }

    /**
     * Dispatch route from collection.
     *
     * @param array $routeData Route info
     * @return array|null Dispatched route info
     */
    public abstract function dispatch($routeData);

    /**
     * Calculate average results
     */
    public function calculate()
    {
        $this->averageResults = [];

        $lowest = [];
        $highest = [];
        // Iterate routes
        foreach ($this->results as $step => $iterations) {
            $lowest[$step] = 1000;
            $highest[$step] = 0;
            $this->averageResults[$step] = 0;

            // Iterate route results
            foreach ($iterations as $elapsed) {
                $this->averageResults[$step] += $elapsed;
                if ($lowest[$step] > $elapsed) {
                    $lowest[$step] = $elapsed;
                }
                if ($highest[$step] < $elapsed) {
                    $highest[$step] = $elapsed;
                }
            }

            $this->averageResults[$step] = number_format($this->averageResults[$step] / $this->iterationCount, 5);
            //$this->averageResults[$step] = number_format($lowest[$step], 5);
            //$this->averageResults[$step] = number_format($highest[$step], 5);
        }
    }
}
