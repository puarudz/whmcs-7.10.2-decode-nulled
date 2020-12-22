<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

class ComposerAutoloaderInit323f23780403defe5009107249f44a2f
{
    private static $loader = NULL;
    public static function loadClassLoader($class)
    {
        if ("Composer\\Autoload\\ClassLoader" === $class) {
            require __DIR__ . "/ClassLoader.php";
        }
    }
    public static function getLoader()
    {
        if (NULL !== self::$loader) {
            return self::$loader;
        }
        spl_autoload_register(array("ComposerAutoloaderInit323f23780403defe5009107249f44a2f", "loadClassLoader"), true, true);
        self::$loader = $loader = new Composer\Autoload\ClassLoader();
        spl_autoload_unregister(array("ComposerAutoloaderInit323f23780403defe5009107249f44a2f", "loadClassLoader"));
        $map = (require __DIR__ . "/autoload_namespaces.php");
        foreach ($map as $namespace => $path) {
            $loader->set($namespace, $path);
        }
        $map = (require __DIR__ . "/autoload_psr4.php");
        foreach ($map as $namespace => $path) {
            $loader->setPsr4($namespace, $path);
        }
        $classMap = (require __DIR__ . "/autoload_classmap.php");
        if ($classMap) {
            $loader->addClassMap($classMap);
        }
        $loader->register(true);
        $includeFiles = (require __DIR__ . "/autoload_files.php");
        foreach ($includeFiles as $file) {
            composerRequire323f23780403defe5009107249f44a2f($file);
        }
        return $loader;
    }
}
function composerRequire323f23780403defe5009107249f44a2f($file)
{
    require $file;
}

?>