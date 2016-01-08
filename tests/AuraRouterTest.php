<?php
/**
 * Created by PhpStorm.
 * User: onysko
 * Date: 08.01.16
 * Time: 10:53
 */

namespace samsonframework\routing\tests;


use Aura\Router\Matcher;
use Aura\Router\RouterContainer;
use samsonframework\routing\generator\Structure;
use samsonframework\routing\Route;
use samsonframework\routing\RouteCollection;
use samsonphp\generator\Generator;
use Zend\Diactoros\ServerRequestFactory;

class AuraRouterTest extends \PHPUnit_Framework_TestCase
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
            //'user-winners-slash'            => ['GET','/user/winners/'],
            'user-by-id'                    => ['GET','/user/{id}', '/user/123'],
            //'user-by-gender-age'            => ['GET','/user/{gender:male|female}/{age}', '/user/male/19d'],
            //'user-by-gender-age-filtered'   => ['GET','/user/{gender:male|female}/{age:[0-9]+}', '/user/female/8'],
            'user-by-id-form'               => ['GET','/user/{id}/form', '/user/123/form'],
            'user-by-id-friends'            => ['GET','/user/{id}/friends', '/user/123/friends'],
            'user-by-id-friends-with-id'    => ['GET','/user/{id}/friends/{groupid}', '/user/123/friends/1'],
            //'entity-by-id-form'             => ['GET','/{entity}/{id}/form'],
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

        $routerContainer = new RouterContainer();
        $map = $routerContainer->getMap();
        foreach ($routeArray as $identifier => $route) {
            $map->get($identifier, $route[1]);
        }
        /** @var Matcher $matcher */
        $matcher = $routerContainer->getMatcher();

        $elapsedAura = [];
        $elapsedSamson = [];

        $iterationsCount = 1000;
        for ($i=0; $i < $iterationsCount; $i++) {
            foreach ($routeArray as $identifier => $routeData) {
                if (isset($routeData[2])) {
                    $request = $this->newRequest($routeData[1]);
                    $timestamp = microtime(true);
                    /** @var \Aura\Router\Route $auraRoute */
                    $auraRoute = $matcher->match($request);
                    $elapsedAura[$routeData[1]][] = microtime(true) - $timestamp;
                    $this->assertEquals($auraRoute->name, $identifier);
                }
            }

            foreach ($routeArray as $identifier => $routeData) {
                if (isset($routeData[2])) {
                    $timestamp = microtime(true);
                    $routeInfo = $routerLogicFunction($routeData[2], $routeData[0]);
                    $elapsedSamson[$routeData[1]][] = microtime(true) - $timestamp;
                    $this->assertEquals($routeInfo[0], $identifier);
                }
            }
        }

        $score = 0;
        $score2 = 0;

        $compareResults = [];

        foreach ($elapsedAura as $step => $iterations) {
            $compareResults['aura'][$step] = 0;
            $compareResults['samson'][$step] = 0;
            for ($i = 0; $i < $iterationsCount; $i++) {
                $compareResults['aura'][$step] += $elapsedAura[$step][$i];
                $compareResults['samson'][$step] += $elapsedSamson[$step][$i];
                // Compare if aura elapsed more time on this iteration for this route
                if ($elapsedAura[$step][$i] > $elapsedSamson[$step][$i]) {
                    $score++;
                } else {
                    $score2++;
                }
            }
            $compareResults['aura'][$step] = number_format($compareResults['aura'][$step] / $iterationsCount, 5);
            $compareResults['samson'][$step] = number_format($compareResults['samson'][$step] / $iterationsCount, 5);
        }

        echo "\n".'Average dispatching results:'."\n";
        foreach ($compareResults['samson'] as $step => $average) {
            echo 'Dispatching "'.$step.'" with average '.$average."\n";
        }
        echo "======================="."\n";
        foreach ($compareResults['aura'] as $step => $average) {
            echo 'Dispatching "'.$step.'" with average '.$average."\n";
        }

        echo "\n".'Summary:'."\n";

        // Except division by zero
        if ($score2 == 0) {
            $score2 = 1;
        }
        echo 'On '.sizeof($routeArray).' routes and '.$iterationsCount.' iterations SamsonFramework\routing was faster '.$score.' times '.
            "\n".'opposite to auraphp\Aura.Router '.$score2.' times, which is '.number_format($score/$score2, 1).' times faster.';
    }
}
