<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit005079f73e0a03883d09f046b711b2d7
{
    public static $prefixLengthsPsr4 = array (
        'O' => 
        array (
            'OsSalahuddin\\CbsClient\\' => 23,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'OsSalahuddin\\CbsClient\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit005079f73e0a03883d09f046b711b2d7::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit005079f73e0a03883d09f046b711b2d7::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit005079f73e0a03883d09f046b711b2d7::$classMap;

        }, null, ClassLoader::class);
    }
}
