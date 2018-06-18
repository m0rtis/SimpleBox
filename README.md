# SimpleBox
[![pipeline status](https://gitlab.com/m0rtis/simplebox/badges/master/pipeline.svg)](https://gitlab.com/m0rtis/simplebox/commits/master)
[![coverage report](https://gitlab.com/m0rtis/simplebox/badges/master/coverage.svg)](https://gitlab.com/m0rtis/simplebox/commits/master)

Simple and little PSR-11 dependency injection container with optional autowiring. This package contains only 4 files, 2 of which implement exception interfaces required by PSR-11. 


## Installing

The best way is to install this package via [composer](https://getcomposer.org):

```
composer require m0rtis/simplebox
```

## Usage

#### Basic

If you don't need autowiring you should to use `m0rtis\SimpleBox\Container` class in your application. 
```php
use m0rtis\SimpleBox\Container

$container = new Container([$arrayOrIteratorWithYourData]);
```
But if you want to use autowiring please feel free to instantiate `m0rtis\SimpleBox\AutowiringContainer` class which extends the basic container class. 
```php
use m0rtis\SimpleBox\AutowiringContainer

$container = new AutowiringContainer([$arrayOrIteratorWithYourData]);
```
If you are using autowiring container you can get any existent (`class_exists === true`) class even if you didn't put it to container before! Just request it by class name and AutowiringContainer will try make an instance for you. We recommend to check if container can instantiate an object for you using PSR-11 `has` method:
```php
if ($autowiringContainer->has(RequiredClass::class) {
    $object = $autowiringContainer->get(RequiredClass::class);
}
```

Please note that SimpleBox container implements ArrayAccess interface. It means you can use SimpleBox container like an array:
```php
if (isset($autowiringContainer[RequiredClass::class])) {
    $object = $autowiringContainer[RequiredClass::class];
}
```

#### Configuration

There is no any separate storage in SimpleBox for configuration. SimpleBox container looks for configuration by item id `config`. This item is expected to be an array or iterable. 
The configuration related to a particular class must be placed under the key with the name of this class or one of the interfaces that it implements.
For example, we have a class:
```php
class SomeAwesomeClass implements AwesomeInterface
{
    private $config;
    
    public function __construct (iterable $config)
    {
        $this->config = $config;
    }
}
```
As you can see it requires some config for instantiation (name of argument MUST be exactly `config`). So you should to place config for it this way:
```php
$container = new AutowiringContainer([
    'config' => [
        SomeAwesomeClass::class => [
            'configKey' => 'configValue'
        ]
    ]
]);
```
or this way:
```php
$container = new AutowiringContainer([
    'config' => [
        AwesomeInterface::class => [
            'configKey' => 'configValue'
        ]
    ]
]);
```
In both cases when you will query `SomeAwesomeClass` from autowiring container requested class will be instantiated with 
his own config:
```php
$config = ['configKey => 'configValue']
```

SimpleBox container itself has the only config option now - `return_shared`.

This is a flag that indicates whether the container will return the same object with the same queries or create a new object each time. 
Default behavior is to return the same object. You can see example in our [tests](tests/ContainerTest.php#L138):
```php
$result1 = $container->get(DependencyTwo::class);
$result2 = $container->get(DependencyTwo::class);

$this->assertSame($result1, $result2);
```
If you want to change default behavior you need to give to container's constructor array or another `iterable` with following structure: 
```php
[
    'config' => [
        'Psr\Container\ContainerInterface::class' => [
            'return_shared' => false
        ]
    ]
]
```

Also you can get a new object instead of the same as previously queried by using `m0rtis\SimpleBox\Container::create(string $id)` method.

#### How to define services

You can use several different ways to define your services in container:
* Callback function that receives the container as argument and returns initialized service (Pimple like way):
```php
$container['serviceName'] = function (ContainerInterface $c) {
       return new AwesomeService($c->get('AwesomeServiceDependency'));
};
```

* Factory class name. Factory should be invokable (implement the magic method `__invoke`), has the word "factory" in its name and should not has any dependencies.
The `__invoke` method should accept a `Psr\Container\ContainerInterface` as only argument:
```php
$container['serviceName'] = ServiceFactory::class;
class ServiceFactory
{
    public function __invoke(ContainerInterface $c)
    {
        return new AwesomeService($c->get('AwesomeServiceDependency'));
    }
}
```

* Any [callable](http://php.net/manual/en/language.types.callable.php). Including static method name as a [string](tests/ContainerTest.php#L92). 

*NOTE*: The exception is the invokable classes (implements `__invoke` magic method) without the word "factory" in its name. Such classes are callable too but SimpleBox container would not call them, just return as is. For example:
```php
$service = $container->get(SomeInvokableClass::class); //$service instanceof SomeInvokableClass

$service = $container->get(ServiceFactory::class): //$service is the result returned by ServiceFactory::__invoke method
```

#### Autowiring

This chapter of README is under construction. But you can see tests as a simple example. And, of course, you can always 
ask me at the [issues](https://gitlab.com/m0rtis/simplebox/issues) page or by [e-mail](mailto:mail#m0rtis.ru).

## Versioning

We use [SemVer](http://semver.org/) for versioning. For the versions available, see the [tags on this repository](https://gitlab.com/m0rtis/simplebox/tags). 

## Author

**Anton Fomichev** aka **m0rtis** - [mail@m0rtis.ru](mailto:mail@m0rtis.ru)


## License

This project is licensed under the Apache 2.0 license - see the [LICENSE](LICENSE) file for details

