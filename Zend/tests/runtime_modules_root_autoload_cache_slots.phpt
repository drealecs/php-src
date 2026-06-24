--TEST--
Runtime modules: module-owned autoloaders registered in root retain their class bindings
--FILE--
<?php
require_once __DIR__ . '/runtime_modules/file_fixture.inc';

class RootCacheFirst {
    public static function name(): string { return __CLASS__; }
}
class RootCacheSecond {
    public static function name(): string { return __CLASS__; }
}
$loader = ($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['loader-owner'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_289'
static fn() => eval(<<<'PHP'
    class RootCacheFirst {
        public static function name(): string { return "module-first"; }
    }
    class RootCacheSecond {
        public static function name(): string { return "module-second"; }
    }
    return static function (string $name): void {
        echo (new RootCacheFirst())::class, "\n";
        echo (new RootCacheSecond())::class, "\n";
        $method = 'name';
        echo RootCacheFirst::$method(), "\n";
        echo RootCacheSecond::$method(), "\n";
    };
PHP)
MODULE_ENTRY_289
, get_defined_vars(), __DIR__);
spl_autoload_register($loader);
class_exists('FirstMissingClass');
class_exists('SecondMissingClass');
?>
--EXPECT--
RootCacheFirst
RootCacheSecond
module-first
module-second
RootCacheFirst
RootCacheSecond
module-first
module-second
