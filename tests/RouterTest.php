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

class RouterTest extends \PHPUnit_Framework_TestCase
{
    public $routes = [
        'main-page'                     => ['GET', '/', '/'],
        'inner-page'                    => ['GET','/{page}', '/text-page'],
        'user-home'                     => ['GET','/user/', '/user/'],
        //'user-home-without-slash'       => ['GET','/user'],
        'user-winners-slash'            => ['GET','/user/winners/', '/user/winners/'],
        'user-by-id'                    => ['GET','/user/{id}', '/user/123'],
        'user-by-gender-age'            => ['GET','/user/{gender:male|female}/{age}', '/user/male/19d'],
        //'user-by-gender-age-filtered'   => ['GET','/user/{gender:male|female}/{age:[0-9]+}', '/user/female/8'],
        'user-by-id-form'               => ['GET','/user/{id}/form', '/user/123/form'],
        'user-by-id-friends'            => ['GET','/user/{id}/friends', '/user/123/friends'],
        'user-by-id-friends-with-id'    => ['GET','/user/{id}/friends/{groupid}', '/user/123/friends/1'],
        'entity-by-id-form'             => ['GET','/{entity}/{id}/form', '/entity/123/form'],
        //'entity-by-id-form-test'        => ['GET','/{id}/test/{page:\d+}'],
        //'two-params'                    => ['GET','/{num}/{page:\d+}'],
        //'user-by-id-node'               => ['GET','/user/{id}/n"$ode'],
        //'user-by-id-node-with-id'       => ['GET','/user/{id}/n"$ode/{param}'],
        'user-with-empty'               => ['GET','/user/{id}/get', '/user/123/get']
    ];

    /** @var RouterImplementation[] */
    public $tests;

    public function testPerformance()
    {
        $this->tests[] = new SamsonImplementation($this->routes);
        $this->tests[] = new SymfonyImplementation($this->routes);
        $this->tests[] = new FastRouteImplementation($this->routes);

        echo '+';
        foreach ($this->tests as $test) {
            echo "\t"."+";
        }
        echo "\n";

        foreach ($this->tests as $test) {
            echo '+ '.$test->name."\t";
        }
        echo "\n";

        foreach ($this->routes as $identifier => $routeData) {
            foreach ($this->tests as $test) {
                $test->iterate($identifier, $routeData);
            }
            foreach ($this->tests as $test) {
                $test->calculate();
            }
            foreach ($this->tests as $test) {
                echo $test->averageResults[$identifier]."\t".'+';
            }
            echo "\n";
        }
    }
}
