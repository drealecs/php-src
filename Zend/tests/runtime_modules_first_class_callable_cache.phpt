--TEST--
Runtime modules: first-class callable caches do not cross module contexts
--FILE--
<?php
require_once __DIR__ . '/runtime_modules/file_fixture.inc';

foreach (['A', 'B'] as $module) {
    ($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$module] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_154'
fn() => eval(
        'function phpt_first_class_callable_target(): string { return '
        . var_export($module, true) . '; }'
    )
MODULE_ENTRY_154
, get_defined_vars(), __DIR__);
}

$a = ($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['A'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_155'
fn() => phpt_first_class_callable_target(...)
MODULE_ENTRY_155
, get_defined_vars(), __DIR__);
$b = ($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['B'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_156'
fn() => phpt_first_class_callable_target(...)
MODULE_ENTRY_156
, get_defined_vars(), __DIR__);

var_dump($a());
var_dump($b());
?>
--EXPECT--
string(1) "A"
string(1) "B"
