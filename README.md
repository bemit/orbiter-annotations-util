# Orbiter\AnnotationUtil

Helper utility for Doctrine\Annotations, uses cached reflection by [scaleupstack/reflection](https://packagist.org/packages/scaleupstack/reflection).

Includes CodeInfo for static code analyzes, for easier, automatic parsing of Annotations and e.g. setup of DI.

- [AnnotationsUtil](#annotationsutil)
- [Example Annotation](#example-annotation)
- [CodeInfo](#code-info---a-file-content-parsing-helper)
- [Example DI Service Setup with CodeInfo](#example-codeinfo-di-service-setup)
- [License](#license)

Install with composer:

    composer require orbiter/annotations-util
 
See [doctrine docs](https://www.doctrine-project.org/projects/annotations.html) for details on what Annotations are and complexer Examples.

## AnnotationsUtil

```php
<?php
use Orbiter\AnnotationsUtil\AnnotationsUtil;

// Add PSR-4 Annotation Namespaces
AnnotationsUtil::registerPsr4Namespace('Lib', __DIR__ . '/lib');

// Use normal doctrine where needed
Doctrine\Common\Annotations\AnnotationReader::addGlobalIgnoredName('dummy');

// Supply a Reader to the Global Util
AnnotationsUtil::useReader(
    AnnotationsUtil::createReader(
        // cache_path or null to disable caching, automatic setup of a `CachedReader` with `FilesystemCache` or `$cache_obj`
        getenv('env') === 'prod' ? __DIR__ . '/tmp/annotations' : null,
        $cache_obj, // optional: custom cache instance, any `doctrine/cache`
    )
);

// Get Annotations
AnnotationsUtil::getClassAnnotations(App\Foo::class): array;// of annotations
AnnotationsUtil::getClassAnnotation(App\Foo::class, Lib\MyAnnotation::class): Lib\MyAnnotation;

AnnotationsUtil::getPropertyAnnotations(App\Foo::class, 'bar'): array;// of annotations
AnnotationsUtil::getPropertyAnnotation(App\Foo::class, 'bar', Lib\MyAnnotation::class): Lib\MyAnnotation;
echo AnnotationsUtil::getPropertyAnnotation(App\Foo::class, 'bar', Lib\MyAnnotation::class)->myProperty;

AnnotationsUtil::getMethodAnnotations(App\Foo::class, 'foo'): array;// of annotations
AnnotationsUtil::getMethodAnnotation(App\Foo::class, 'foo', Lib\MyAnnotation::class): Lib\MyAnnotation;
echo AnnotationsUtil::getMethodAnnotation(App\Foo::class, 'foo', Lib\MyAnnotation::class)->myProperty;

// uses only cached Reflection, without annotations
AnnotationsUtil::getClass(App\Foo::class): ReflectionClass;
AnnotationsUtil::getProperty(App\Foo::class, 'bar'): ReflectionProperty;
AnnotationsUtil::getMethod(App\Foo::class, 'foo'): ReflectionMethod;

AnnotationsUtil::setPropertyValue(App\Foo::class, 'bar');
AnnotationsUtil::setStaticPropertyValue(App\Foo::class, 'bar_tastic');

AnnotationsUtil::invokeMethod(App\Foo::class, 'fooTastic');
AnnotationsUtil::invokeStaticMethod(App\Foo::class, 'foo');
```

### Example Annotation

Define your annotation, remember to specify it in the Annotation loaders - normal **autoloading doesn't work** for classes using `@Annotation`!

```php
<?php
namespace Lib;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 */
final class MyAnnotation {
    public $myProperty;
}
```

Using an annotation in a class:

```php
<?php
namespace App;

use Lib\MyAnnotation;

class Foo {
    /**
     * @MyAnnotation(myProperty="demo-value")
     */
    private $bar;
}
```

Get value of the Annotation:

```php
<?php
use Orbiter\AnnotationsUtil\AnnotationsUtil;

// this prints "demo-value"
echo AnnotationsUtil::getMethodAnnotation(App\Foo::class, 'bar', Lib\MyAnnotation::class)->myProperty;
/**
 * @var Lib\MyAnnotation $annotation this variable is the Annotation instance and contains also it's data
 */
$annotation = AnnotationsUtil::getMethodAnnotation(App\Foo::class, 'bar', Lib\MyAnnotation::class);
// this is the recommended way to use the properties
echo $annotation->myProperty;
```

This example is a summarized version, using this utility to read in the end, from [Doctrine\Annotations: Create Annotations](https://www.doctrine-project.org/projects/doctrine-annotations/en/1.6/index.html#introduction).

## Code Info - a file Content parsing helper

It helps to parse PHP content of files to search e.g. for full qualified names, caching the parsed result after processing.

Can be used e.g. to get all classes in a folder to add as services in DI or to parse all Annotations of those classes - without initiating/importing the classes.

```php
<?php
use Orbiter\AnnotationsUtil\CodeInfo;
use Orbiter\AnnotationsUtil\CodeInfoData;

$code_info = new CodeInfo();
if(getenv('env') === 'prod') {
    // absolute file to cache, if cache exists, it will not be re-freshed.
    // Delete the file for a new cache
    $code_info->enableFileCache(__DIR__ . '/tmp/codeinfo.cache');
}

// Add a group of dirs, named so you can parse dirs multiple times for different reasons
$code_info->defineDirs('services', [
    __DIR__ . '/src',
    __DIR__ . '/Lib',
]);

// Change Extensions that will be parsed, default includes `php`
$code_info->addExtension('php5');
$code_info->rmExtension('php');

// parse defined folders
$code_info->process();

// retrieve array of all class names found for this group of dirs
$services = $code_info->getClassNames('services');

$services_methods = $code_info->getClassMethods('services');
/*
 * array with all classes and one array for `public` and one for `static` 
 * $services_methods = [
 *     ClassName::class => [
 *         'public' => [
 *             '__construct',
 *             'handle',
 *         ],
 *         'static' => [
 *         ]
 *     ],
 * ];
 */

$services_properties = $code_info->getClassProperties('services');
// properties array structure is like method structure
```

Things needs to be done:

- cache parsed result of file or dir and use it when same file/dir get's scanned again
    - currently scans dirs multiple times and also each file
    - after first cache build this is no problem
- add control of recursive scan of dir, editable per dir per group
- add interface `getAttribute` + implement in `CodeInfoData` 
    
### Example CodeInfo DI Service Setup

Example: use `$code_info` now to add all found classes to [PHP-DI Container](http://php-di.org), for automatic autowired folders and getting them into e.g. the cached container.

> Notes from PHP-DI docs:
>
>"Autowired classes that are not listed in the configuration cannot be compiled since PHP-DI doesn't know about them."
>
> Also DI Annotations won't work for unknown classes:
> "Currently PHP-DI does not traverse directories to find autowired or annotated classes automatically."
>
> See [PHP-DI Performance](http://php-di.org/doc/performances.html)
 
```php
<?php
use DI\ContainerBuilder;
use function DI\autowire;

$container_builder = new ContainerBuilder();

// add CodeInfo itself as service
$definitions = [
    CodeInfo::class => $code_info,
];

// autowire all found services (see above on how to find them)
foreach($services as $service) {
    $definitions[$service] = autowire($service);
}

$container_builder->addDefinitions($definitions);

$container = $container_builder->build();
```

## Take a Look

Want to build console apps with dependency injection and annotations? Use this app skeleton: [elbakerino/console](https://github.com/elbakerino/console-di-annotations), powered by PHP-DI, uses Doctrine\Annotations with this package.

Build event and middleware based apps with [Satellite](https://github.com/bemit/satellite-app), also DI enabled and  annotation with this package.

## License

This project is free software distributed under the **MIT License**.

See: [LICENSE](LICENSE).

### Contributors

By committing your code to the code repository you agree to release the code under the MIT License attached to the repository without the expectation of consideration.

***

Author: [Michael Becker](https://mlbr.xyz)
