<?php
/**
 * Created by PhpStorm.
 * User: onysko
 * Date: 08.01.2016
 * Time: 14:11
 */

namespace samsonframework\routing\tests;

require_once "RouterImplementation.php";
require_once "SamsonImplementation.php";
require_once "SymfonyImplementation.php";
require_once "FastRouteImplementation.php";
require_once "AuraImplementation.php";
require_once "AltoImplementation.php";

class RouterTest extends \PHPUnit_Framework_TestCase
{
    public $routes = [
        'main-page'                     => ['GET', '/', '/'],
        'inner-page'                    => ['GET','/{page}', '/text-page'],
        'user-home'                     => ['GET','/user/', '/user/'],
        'user-home'                     => ['GET','/user/', '/user/'],
        'user-winners-slash'            => ['GET','/user/winners/', '/user/winners/'],
        'user-by-id'                    => ['GET','/user/{id}', '/user/123'],
        'user-by-gender-age'            => ['GET','/user/{gender:male|female}/{age}', '/user/male/19d'],
        'user-by-gender-age-filtered'   => ['GET','/user/{gender:male|female}/{age:[0-9]+}', '/user/female/8'],
        'user-by-id-form'               => ['GET','/user/{id}/form', '/user/123/form'],
        'user-by-id-friends'            => ['GET','/user/{id}/friends', '/user/123/friends'],
        'user-by-id-friends-with-id'    => ['GET','/user/{id}/friends/{groupid}', '/user/123/friends/1'],
        'entity-by-id-form'             => ['GET','/{entity}/{id}/form', '/entity/123/form'],
        'entity-by-id-form-test'        => ['GET','/{id}/test/{page:\d+}', '/123/test/11'],
        'two-params'                    => ['GET','/{num}/{page:\d+}', '123434/12'],
        'user-by-id-node'               => ['GET','/user/{id}/n"$ode', '/user/321/n"$ode'],
        'user-by-id-node-with-id'       => ['GET','/user/{id}/n"$ode/{param}', '/user/321/n"$ode/1a'],
        'user-with-empty'               => ['GET','/user/{id}/get', '/user/123/get']
    ];

    /** @var RouterImplementation[] */
    public $tests;

    public function testPerformance()
    {
        $this->tests[] = new SamsonImplementation($this->routes);
        $this->tests[] = new SymfonyImplementation($this->routes);
        $this->tests[] = new FastRouteImplementation($this->routes);
        $this->tests[] = new AuraImplementation($this->routes);
        $this->tests[] = new AltoImplementation($this->routes);

        $table = ['header' => ['route']];
        foreach ($this->tests as $test) {
            $table['header'][] = $test->name;
        }
        foreach ($this->routes as $identifier => $routeData) {
            $table[$identifier][] = $identifier;
            foreach ($this->tests as $test) {
                $test->iterate($identifier, $routeData);
            }
            foreach ($this->tests as $test) {
                $test->calculate();
                $table[$identifier][$test->name] = $test->averageResults[$identifier];
            }
        }
        echo $this->table($table);
    }

    function table($data) {

        // Find longest string in each column
        $columns = [];
        foreach ($data as $row_key => $row) {
            foreach ($row as $cell_key => $cell) {
                $length = strlen($cell);
                if (empty($columns[$cell_key]) || $columns[$cell_key] < $length) {
                    $columns[$cell_key] = $length;
                }
            }
        }

        // Output table, padding columns
        $table = '';
        foreach ($data as $row_key => $row) {
            $row_min = 1000;
            foreach ($row as $item) {
                if (is_numeric($item) && $item < $row_min) {
                    $row_min = $item;
                }
            }
            foreach ($row as $cell_key => $cell) {
                if ($cell == '1,000.00000') {
                    $cell = '-';
                }
                if ($cell == $row_min) {
                    $table .= "\e[1;32m". str_pad($cell, $columns[$cell_key]) . '   ';
                } else {
                    $table .= "\033[0m". str_pad($cell, $columns[$cell_key]) . '   ';
                }
            }
            $table .= PHP_EOL;
        }
        return $table;

    }
}
