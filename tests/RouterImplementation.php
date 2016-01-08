<?php
/**
 * Created by PhpStorm.
 * User: onysko
 * Date: 08.01.2016
 * Time: 13:32
 */

namespace samsonframework\routing\tests;


class RouterImplementation
{
    /** @var int Count of iterations for each route */
    public $iterationCount = 1000;

    /** @var array Results for each route */
    public $results = [];

    /** @var array Average results for each route */
    public $averageResults = [];

    /** @var string Test name */
    public $name = 'Test';

    public $collection;

    public function __construct($routes)
    {
    }

    public function iterate($identifier, $routeData)
    {
        for ($i = 0; $i < $this->iterationCount; $i++) {
            $timestamp = microtime(true);
            $routeInfo = $this->dispatch($routeData);
            $this->results[$identifier][] = !empty($routeInfo) ? microtime(true) - $timestamp : 1000;
        }
    }

    /**
     * Dispatch route from collection
     * @param array $routeData Route info
     * @return string
     */
    public function dispatch($routeData)
    {
        return '';
    }

    /**
     * Calculate average results
     */
    public function calculate()
    {
        $this->averageResults = [];

        foreach ($this->results as $step => $iterations) {
            $this->averageResults[$step] = 0;
            for ($i = 0; $i < $this->iterationCount; $i++) {
                $this->averageResults[$step] += $this->results[$step][$i];
            }

            $this->averageResults[$step] = number_format($this->averageResults[$step] / $this->iterationCount, 5);
        }
    }
}
