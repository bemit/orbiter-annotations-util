# Orbiter\AnnotationUtil

Helper utility for Doctrine\Annotations, uses cached reflection by [scaleupstack/reflection](https://packagist.org/packages/scaleupstack/reflection).

Includes CodeInfo for static code analyzes, for easier, automatic parsing of Annotations and e.g. setup of DI.

- [AnnotationsUtil](#annotationsutil)
- [Example Annotation](#example-annotation)
- [CodeInfo](#code-info---a-file-content-parsing-helper)
- [License](#license)

Install with composer:

    composer require orbiter/annotations-util
 
See [doctrine docs](https://www.doctrine-project.org/projects/annotations.html) for details on what Annotations are and complexer Examples.

## AnnotationsUtil

```php
<?php
use Orbiter\AnnotationsUtil\AnnotationUtil;
use Orbiter\AnnotationsUtil\CachedReflection;

// Add PSR-4 Annotation Namespaces
AnnotationUtil::registerPsr4Namespace('Lib', __DIR__ . '/lib');

// uses only cached Reflection, without annotations
CachedReflection::getClass(App\Foo::class): ReflectionClass;
CachedReflection::getProperty(App\Foo::class, 'bar'): ReflectionProperty;
CachedReflection::getMethod(App\Foo::class, 'foo'): ReflectionMethod;

CachedReflection::setPropertyValue(App\Foo::class, 'bar');
CachedReflection::setStaticPropertyValue(App\Foo::class, 'bar_tastic');

CachedReflection::invokeMethod(App\Foo::class, 'fooTastic');
CachedReflection::invokeStaticMethod(App\Foo::class, 'foo');

// Use normal doctrine where needed
Doctrine\Common\Annotations\AnnotationReader::addGlobalIgnoredName('dummy');

$annotations = new Orbiter\AnnotationsUtil\AnnotationReader(
    new Doctrine\Common\Annotations\IndexedReader(
        new Doctrine\Common\Annotations\AnnotationReader()
    )
);

// Get Annotations
$annotations->getClassAnnotations(App\Foo::class): array;// of annotations
$annotations->getClassAnnotation(App\Foo::class, Lib\MyAnnotation::class): Lib\MyAnnotation;

$annotations->getPropertyAnnotations(App\Foo::class, 'bar'): array;// of annotations
$annotations->getPropertyAnnotation(App\Foo::class, 'bar', Lib\MyAnnotation::class): Lib\MyAnnotation;
echo $annotations->getPropertyAnnotation(App\Foo::class, 'bar', Lib\MyAnnotation::class)->myProperty;

$annotations->getMethodAnnotations(App\Foo::class, 'foo'): array;// of annotations
$annotations->getMethodAnnotation(App\Foo::class, 'foo', Lib\MyAnnotation::class): Lib\MyAnnotation;
echo $annotations->getMethodAnnotation(App\Foo::class, 'foo', Lib\MyAnnotation::class)->myProperty;
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

// this prints "demo-value"
echo $annotations->getMethodAnnotation(App\Foo::class, 'bar', Lib\MyAnnotation::class)->myProperty;
/**
 * @var Lib\MyAnnotation $annotation this variable is the Annotation instance and contains also it's data
 */
$annotation = $annotations->getMethodAnnotation(App\Foo::class, 'bar', Lib\MyAnnotation::class);
// this is the recommended way to use the properties
echo $annotation->myProperty;
```

This example is a summarized version, using this utility to read in the end, from [Doctrine\Annotations: Create Annotations](https://www.doctrine-project.org/projects/doctrine-annotations/en/1.6/index.html#introduction).

## Code Info - a file Content parsing helper

It helps to parse PHP content of files to search e.g. for full qualified names, caching the parsed result after processing.

Can be used e.g. to get all classes in a folder to search for Annotations of those classes - without initiating/importing the classes.

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
$code_info->defineDirs('commands', [
    __DIR__ . '/app/Commands',
]);

// Change Extensions that will be parsed, default includes `php`
$code_info->addExtension('php5');
$code_info->rmExtension('php');

// parse defined folders
$code_info->process();

// retrieve array of all class names found for this group of dirs
$commands = $code_info->getClassNames('commands');

$commands_methods = $code_info->getClassMethods('commands');
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

$commands_properties = $code_info->getClassProperties('commands');
// properties array structure is like method structure
```

## Open todos

- cache parsed result of file or dir and use it when same file/dir get's scanned again
    - currently scans dirs multiple times and also each file
- add control of recursive scan of dir, editable per dir per group
- add interface `getAttribute` + implement in `CodeInfoData` 

## Take a Look

Want to build console apps with dependency injection and annotations? Use this app skeleton: [elbakerino/console](https://github.com/elbakerino/console-di-annotations), powered by PHP-DI, uses Doctrine\Annotations with this package.

Build event and middleware based apps with [Satellite](https://github.com/bemit/satellite-app), DI enabled and with annotations for cli commands and routings..

## License

This project is free software distributed under the **MIT License**.

See: [LICENSE](LICENSE).

### Contributors

By committing your code to the code repository you agree to release the code under the MIT License attached to the repository without the expectation of consideration.

***

Author: [Michael Becker](https://mlbr.xyz)
