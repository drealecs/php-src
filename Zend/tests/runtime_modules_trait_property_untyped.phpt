--TEST--
Runtime modules: duplicate untyped trait properties are compatible across module owners
--FILE--
<?php
require_once __DIR__ . '/runtime_modules/file_fixture.inc';

($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['property-consumer'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_358'
static fn() => \RuntimeModule::current()->addDependency($GLOBALS["runtime_modules"]['property-trait'] ??= new \RuntimeModule())
MODULE_ENTRY_358
, get_defined_vars(), __DIR__);
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['property-trait'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_359'
static fn() => eval(<<<'PHP'
    trait UntypedProperties {
        public $value = 1;
        public static $staticValue = 2;
    }
PHP)
MODULE_ENTRY_359
, get_defined_vars(), __DIR__);
$object = ($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['property-consumer'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_360'
static function (): object {
    class UntypedConsumer {
        use UntypedProperties;
        public $value = 1;
        public static $staticValue = 2;
    }
    return new UntypedConsumer();
}
MODULE_ENTRY_360
, get_defined_vars(), __DIR__);
var_dump($object->value, $object::$staticValue);
?>
--EXPECT--
int(1)
int(2)
