--TEST--
Runtime modules: module-compiled autoloaders have separate class caches in root context
--FILE--
<?php
class RootCacheFirst {
    public static function name(): string { return __CLASS__; }
}
class RootCacheSecond {
    public static function name(): string { return __CLASS__; }
}
$loader = module_run('loader-owner', static fn() => eval(<<<'PHP'
    return static function (string $name): void {
        echo (new RootCacheFirst())::class, "\n";
        echo (new RootCacheSecond())::class, "\n";
        $method = 'name';
        echo RootCacheFirst::$method(), "\n";
        echo RootCacheSecond::$method(), "\n";
    };
PHP));
spl_autoload_register($loader);
class_exists('FirstMissingClass');
class_exists('SecondMissingClass');
?>
--EXPECT--
RootCacheFirst
RootCacheSecond
RootCacheFirst
RootCacheSecond
RootCacheFirst
RootCacheSecond
RootCacheFirst
RootCacheSecond
