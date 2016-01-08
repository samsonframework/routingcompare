<?php
/**
 * Created by PhpStorm.
 * User: onysko
 * Date: 08.01.16
 * Time: 10:53
 */

namespace samsonframework\routing\tests;

use samsonframework\routing\generator\Structure;
use samsonframework\routing\Route;
use samsonframework\routing\RouteCollection;
use samsonphp\generator\Generator;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Zend\Diactoros\ServerRequestFactory;

class SymfonyRouterTest extends \PHPUnit_Framework_TestCase
{
    protected function newRequest($path, array $server = [])
    {
        $server['REQUEST_URI'] = $path;
        $server = array_merge($_SERVER, $server);
        return ServerRequestFactory::fromGlobals($server);
    }

    public function baseCallback()
    {
        return __FUNCTION__;
    }

    public function testPerformance()
    {
        // Create routes descriptions with identifiers
        $routeArray = [
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

        // Create samsonframework\routing routes collection
        $routes = new RouteCollection();
        foreach ($routeArray as $identifier => $routeData) {
            $routes->add(new Route($routeData[1], array($this, 'baseCallback'), $identifier, $routeData[0]));
        }

        // Generate routing logic
        $generator = new Structure($routes, new Generator());
        $routerLogicFunction = '__router'.rand(0, 1000);
        $routerLogic = $generator->generate($routerLogicFunction);

        // Create real file for debugging
        file_put_contents(__DIR__.'/testLogic.php', '<?php '."\n".$routerLogic);
        require(__DIR__.'/testLogic.php');

        $routerContainer = new \Symfony\Component\Routing\RouteCollection();
        foreach ($routeArray as $identifier => $route) {
            $routerContainer->add($identifier, new \Symfony\Component\Routing\Route($route[1]));
        }
        $context = new RequestContext();
        $matcher = new UrlMatcher($routerContainer, $context);

        $elapsedSymfony = [];
        $elapsedSamson = [];

        $iterationsCount = 1000;
        for ($i=0; $i < $iterationsCount; $i++) {
            foreach ($routeArray as $identifier => $routeData) {
                $timestamp = microtime(true);
                $symfonyRoute = $matcher->match($routeData[1]);
                $elapsedSymfony[$routeData[1]][] = microtime(true) - $timestamp;
                $this->assertEquals($symfonyRoute['_route'], $identifier);
            }

            foreach ($routeArray as $identifier => $routeData) {
                $timestamp = microtime(true);
                $routeInfo = $routerLogicFunction($routeData[2], $routeData[0]);
                $elapsedSamson[$routeData[1]][] = microtime(true) - $timestamp;
                $this->assertEquals($routeInfo[0], $identifier);
            }
        }

        $score = 0;
        $score2 = 0;

        $compareResults = [];

        foreach ($elapsedSymfony as $step => $iterations) {
            $compareResults['symfony'][$step] = 0;
            $compareResults['samson'][$step] = 0;
            for ($i = 0; $i < $iterationsCount; $i++) {
                $compareResults['symfony'][$step] += $elapsedSymfony[$step][$i];
                $compareResults['samson'][$step] += $elapsedSamson[$step][$i];
                // Compare if symfony elapsed more time on this iteration for this route
                if ($elapsedSymfony[$step][$i] > $elapsedSamson[$step][$i]) {
                    $score++;
                } else {
                    $score2++;
                }
            }
            $compareResults['symfony'][$step] = number_format($compareResults['symfony'][$step] / $iterationsCount, 5);
            $compareResults['samson'][$step] = number_format($compareResults['samson'][$step] / $iterationsCount, 5);
        }

        echo "\n".'Average dispatching results:'."\n";
        foreach ($compareResults['samson'] as $step => $average) {
            echo 'Dispatching "'.$step.'" with average '.$average."\n";
        }
        echo "======================="."\n";
        foreach ($compareResults['symfony'] as $step => $average) {
            echo 'Dispatching "'.$step.'" with average '.$average."\n";
        }

        echo "\n".'Summary:'."\n";

        // Except division by zero
        if ($score2 == 0) {
            $score2 = 1;
        }
        echo 'On '.sizeof($routeArray).' routes and '.$iterationsCount.' iterations SamsonFramework\routing was faster '.$score.' times '.
            "\n".'opposite to symfony\routing '.$score2.' times, which is '.number_format($score/$score2, 1).' times faster.';
    }
}
