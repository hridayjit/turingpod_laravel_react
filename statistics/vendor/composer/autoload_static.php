<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit1ba0f715c031674220ef2a8b8d883f1f
{
    public static $prefixLengthsPsr4 = array (
        'S' => 
        array (
            'StatisticsLibrary\\' => 18,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'StatisticsLibrary\\' => 
        array (
            0 => __DIR__ . '/../..' . '/statisticsLibrary',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
        'StatisticsLibrary\\Lib\\DistributionLibrary' => __DIR__ . '/../..' . '/statisticsLibrary/Lib/DistributionLibrary.php',
        'StatisticsLibrary\\Lib\\HypothesisLibrary' => __DIR__ . '/../..' . '/statisticsLibrary/Lib/HypothesisLibrary.php',
        'StatisticsLibrary\\Lib\\MethodsLibrary' => __DIR__ . '/../..' . '/statisticsLibrary/Lib/MethodsLibrary.php',
        'StatisticsLibrary\\Lib\\OperationsLibrary' => __DIR__ . '/../..' . '/statisticsLibrary/Lib/OperationsLibrary.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit1ba0f715c031674220ef2a8b8d883f1f::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit1ba0f715c031674220ef2a8b8d883f1f::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit1ba0f715c031674220ef2a8b8d883f1f::$classMap;

        }, null, ClassLoader::class);
    }
}
