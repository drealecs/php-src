--TEST--
Runtime modules: handles cannot be cloned, serialized, structurally equated or reinitialized
--FILE--
<?php
require_once __DIR__ . '/runtime_modules/file_fixture.inc';

$module = new RuntimeModule();
var_dump($module == new RuntimeModule(), $module == $module);
foreach ([
    fn() => clone $module,
    fn() => serialize($module),
    fn() => unserialize('O:13:"RuntimeModule":0:{}'),
    fn() => $module->__construct(),
    fn() => RuntimeModule::current()->__construct(),
    fn() => $module->label = 'test',
    fn() => (new ReflectionClass(RuntimeModule::class))->newInstanceWithoutConstructor(),
    fn() => $module->require([]),
    fn() => $module->addDependency('test'),
] as $operation) {
    try {
        $operation();
    } catch (Throwable $error) {
        echo $error::class, "\n";
    }
}
var_dump((new ReflectionClass(RuntimeModule::class))->isFinal());
?>
--EXPECT--
bool(false)
bool(true)
Error
Exception
Exception
Error
Error
Error
ReflectionException
TypeError
TypeError
bool(true)
