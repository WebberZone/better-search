<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit669a86d3280b733a48a4a4e6dec9f391
{
    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->classMap = ComposerStaticInit669a86d3280b733a48a4a4e6dec9f391::$classMap;

        }, null, ClassLoader::class);
    }
}
