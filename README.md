# Orbiter\AnnotationUtil

A Helper util for Doctrine\Annotations, uses cached reflection by [scaleupstack/reflection](https://packagist.org/packages/scaleupstack/reflection).

Install with composer:

    composer require orbiter/annotations-util  
 
See doctrine docs for details:

- [Create Annotation](https://www.doctrine-project.org/projects/doctrine-annotations/en/1.6/index.html#reading-annotations)
- [Handling Annotation](https://www.doctrine-project.org/projects/doctrine-annotations/en/1.6/annotations.html#handling-annotations), how to setup Annotation loaders

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
        // cache_path
        getenv('env') === 'prod' ? __DIR__ . '/tmp/annotations' : null, getenv('env') === 'prod',
        $cache_obj, // optional: custom cache instance, any `doctrine/cache`
    )
);

// Get Annotations
echo AnnotationsUtil::getClassAnnotation(App\Foo::class, \Lib\MyAnnotation::class)->myProperty;
AnnotationsUtil::getClassAnnotations(App\Foo::class): array;

echo AnnotationsUtil::getPropertyAnnotation(App\Foo::class, 'foo', \Lib\MyAnnotation::class)->myProperty;
AnnotationsUtil::getPropertyAnnotations(App\Foo::class, 'foo'): array;

echo AnnotationsUtil::getMethodAnnotation(App\Foo::class, 'baz', \Lib\MyAnnotation::class)->myProperty;
AnnotationsUtil::getMethodAnnotations(App\Foo::class, 'baz'): array;

// Use cached Reflection
AnnotationsUtil::getClass(App\Foo::class): \ReflectionClass;
AnnotationsUtil::getProperty(App\Foo::class, 'foo'): \ReflectionProperty;
AnnotationsUtil::getMethod(App\Foo::class, 'bar'): \ReflectionMethod;
```

## Take a Look

Build event and middleware based apps with [Satellite](https://github.com/bemit/satellite-app), add annotation with this package.

## License

This project is free software distributed under the **MIT License**.

See: [LICENSE](LICENSE).

### Contributors

By committing your code to the code repository you agree to release the code under the MIT License attached to the repository without the expectation of consideration.

***

Maintained by [Michael Becker](https://mlbr.xyz)
