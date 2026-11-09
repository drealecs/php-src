--TEST--
Runtime modules: module-owned SPL autoloaders
--FILE--
<?php
require_once __DIR__ . '/runtime_modules/file_fixture.inc';

$m1 = 'phpt_autoload_m1';
$m2 = 'phpt_autoload_m2';
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$m1] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_1'
function () {
    spl_autoload_register(function (string $class): void {
        if ($class === 'PhptAutoloadThing') {
            eval('class PhptAutoloadThing { function id() { return 1; } }');
        }
    });
}
MODULE_ENTRY_1
, get_defined_vars(), __DIR__);
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$m2] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_2'
function () {
    spl_autoload_register(function (string $class): void {
        if ($class === 'PhptAutoloadThing') {
            eval('class PhptAutoloadThing { function id() { return 2; } }');
        }
    });
}
MODULE_ENTRY_2
, get_defined_vars(), __DIR__);
var_dump(count(spl_autoload_functions()));
var_dump(count(($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$m1] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_3'
fn() => spl_autoload_functions()
MODULE_ENTRY_3
, get_defined_vars(), __DIR__)));
var_dump(count(($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$m2] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_4'
fn() => spl_autoload_functions()
MODULE_ENTRY_4
, get_defined_vars(), __DIR__)));
var_dump(($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$m1] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_5'
fn() => (new PhptAutoloadThing())->id()
MODULE_ENTRY_5
, get_defined_vars(), __DIR__));
var_dump(($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$m2] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_6'
fn() => (new PhptAutoloadThing())->id()
MODULE_ENTRY_6
, get_defined_vars(), __DIR__));
?>
--EXPECT--
int(0)
int(1)
int(1)
int(1)
int(2)
