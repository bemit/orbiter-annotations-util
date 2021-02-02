<?php

namespace Orbiter\AnnotationsUtil;

use Doctrine\Common\Annotations;

/**
 * Helper Class to for Doctrine\Annotation
 *
 * @package Orbiter\AnnotationsUtil
 */
class AnnotationUtil {
    /**
     * Registers a PSR-4 autoloading namespace for annotations, as the default only includes an PSR-0 compatible
     *
     * @param $namespace
     * @param $dir
     */
    public static function registerPsr4Namespace($namespace, $dir) {
        Annotations\AnnotationRegistry::registerLoader(static function($class) use ($namespace, $dir) {
            if(strpos($class, $namespace) === 0) {
                // be sure to that it fails silently
                $file = rtrim($dir, "/\\") . '/' . str_replace("\\", DIRECTORY_SEPARATOR, str_replace($namespace, '', $class)) . '.php';

                if(file_exists($file)) {
                    require_once $file;

                    return true;
                }
                // it must fail silently
            }
        });
    }
}
