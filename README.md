# SamsonFramework Routing implementation compare to other popular implementations

##Installation
Clone this project, run ```composer install```.

##Testing
For running performance comparison tests:
```$ vendor/bin/phpunit```

##Contributing
Feel free to fork and create pull requests at any time. 

##License
Open Software License ("OSL") v 3.0. Please see License File for more information.

##Implemented routers
Implemented popular routing implementations in this tests: 
* [SamsonFramework routing package](http://github.com/samsonframework/routing)
* [FastRoute package](http://github.com/nikic/FastRoute)
* [Aura.Route package](http://github.com/auraphp/Aura.Router)
* [Symfony Routing package](http://github.com/symfony/routing)
* [Alto Routing package](http://github.com/dannyvankooten/AltoRouter)

###Adding router implementation for testing
All router test implementation should extend abstract class ```samsonframework\routing\tests\RouterImplementation```.
Usually you need to implement ```__constructor($routes)``` and dispatcher:
```php
public abstract function dispatch($routeData);
```
See other routers test implementation for example.

###Adding new routes for testing among implemented routes
If you see that we have missed some specific routes types and/or patterns please add them to routes collection
in ```samsonframework\routing\tests\RouterTest::$routes``` field.

[SamsonOS](http://samsonos.com)
