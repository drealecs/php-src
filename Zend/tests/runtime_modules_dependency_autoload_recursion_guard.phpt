--TEST--
Runtime modules: dependency autoload recursion is guarded in the dependency context
--FILE--
<?php
require_once __DIR__ . '/runtime_modules/file_fixture.inc';

$events = [];
$a = 'phpt_dependency_autoload_guard_a';
$b = 'phpt_dependency_autoload_guard_b';

($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$a] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_118'
fn() => \RuntimeModule::current()->addDependency($GLOBALS["runtime_modules"][$b] ??= new \RuntimeModule())
MODULE_ENTRY_118
, get_defined_vars(), __DIR__);
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$b] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_119'
function () use (&$events): void {
    spl_autoload_register(function (string $class) use (&$events): void {
        $events[] = $class;
        $events[] = class_exists($class) ? 'recursive=true' : 'recursive=false';
        eval('class PhptDependencyAutoloadGuardThing {}');
    });
}
MODULE_ENTRY_119
, ['events'=>&$events] + get_defined_vars(), __DIR__);

var_dump(($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$a] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_120'
fn() => class_exists('PhptDependencyAutoloadGuardThing')
MODULE_ENTRY_120
, get_defined_vars(), __DIR__));
var_dump($events);
?>
--EXPECT--
bool(true)
array(2) {
  [0]=>
  string(32) "PhptDependencyAutoloadGuardThing"
  [1]=>
  string(15) "recursive=false"
}
