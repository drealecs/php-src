--TEST--
Runtime modules: direct dependency autoloaders are visible to dependant modules
--FILE--
<?php
require_once __DIR__ . '/runtime_modules/file_fixture.inc';

$dependency = 'phpt_dep_autoload_dependency';
$main = 'phpt_dep_autoload_main';

($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$main] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_114'
fn() => \RuntimeModule::current()->addDependency($GLOBALS["runtime_modules"][$dependency] ??= new \RuntimeModule())
MODULE_ENTRY_114
, get_defined_vars(), __DIR__);

($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$dependency] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_115'
function (): void {
    spl_autoload_register(function (string $class): void {
        if ($class === 'PhptDependencyAutoloadThing') {
            eval('class PhptDependencyAutoloadThing { public static function id(): string { return "dependency"; } }');
        }
    });
}
MODULE_ENTRY_115
, get_defined_vars(), __DIR__);

var_dump(($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$main] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_116'
fn() => class_exists('PhptDependencyAutoloadThing')
MODULE_ENTRY_116
, get_defined_vars(), __DIR__));
var_dump(($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$main] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_117'
fn() => PhptDependencyAutoloadThing::id()
MODULE_ENTRY_117
, get_defined_vars(), __DIR__));
var_dump(class_exists('PhptDependencyAutoloadThing', false));
?>
--EXPECT--
bool(true)
string(10) "dependency"
bool(false)
