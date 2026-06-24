--TEST--
Runtime modules: opaque handles, current context and receiver-based dependencies
--FILE--
<?php
require_once __DIR__ . '/runtime_modules/file_fixture.inc';

$root = RuntimeModule::current();
$module = new RuntimeModule();
$other = new RuntimeModule();
$a = 2;
$b = 3;
var_dump(($GLOBALS["module_fixture"])($module, <<<'MODULE_ENTRY_1'
fn() => $a + $b
MODULE_ENTRY_1
, get_defined_vars(), __DIR__));
var_dump(RuntimeModule::current() === $root, $module != $other);
var_dump(($GLOBALS["module_fixture"])($module, <<<'MODULE_ENTRY_2'
fn() => RuntimeModule::current()
MODULE_ENTRY_2
, get_defined_vars(), __DIR__) === $module);
($GLOBALS["module_fixture"])($module, <<<'MODULE_ENTRY_3'
fn() => eval('class PhptApiHandleReuse {}')
MODULE_ENTRY_3
, get_defined_vars(), __DIR__);
var_dump(($GLOBALS["module_fixture"])($module, <<<'MODULE_ENTRY_4'
fn() => class_exists('PhptApiHandleReuse', false)
MODULE_ENTRY_4
, get_defined_vars(), __DIR__));
var_dump(($GLOBALS["module_fixture"])($other, <<<'MODULE_ENTRY_5'
fn() => class_exists('PhptApiHandleReuse', false)
MODULE_ENTRY_5
, get_defined_vars(), __DIR__));

$main = new RuntimeModule();
$dep = new RuntimeModule();
($GLOBALS["module_fixture"])($other, <<<'MODULE_ENTRY_6'
fn() => $main->addDependency($dep)
MODULE_ENTRY_6
, get_defined_vars(), __DIR__);
($GLOBALS["module_fixture"])($dep, <<<'MODULE_ENTRY_7'
fn() => eval('class PhptApiDependency {}')
MODULE_ENTRY_7
, get_defined_vars(), __DIR__);
var_dump(($GLOBALS["module_fixture"])($main, <<<'MODULE_ENTRY_8'
fn() => class_exists('PhptApiDependency', false)
MODULE_ENTRY_8
, get_defined_vars(), __DIR__));
var_dump(($GLOBALS["module_fixture"])($other, <<<'MODULE_ENTRY_9'
fn() => class_exists('PhptApiDependency', false)
MODULE_ENTRY_9
, get_defined_vars(), __DIR__));
$main->addDependency($main);
$main->addDependency($dep);
$root->addDependency($root);
$root->addDependency($dep);
var_dump(class_exists('PhptApiDependency', false));
var_dump(($GLOBALS["module_fixture"])($other, <<<'MODULE_ENTRY_11'
fn() => ($GLOBALS["module_fixture"])($root, <<<'MODULE_ENTRY_10'
fn() => RuntimeModule::current()
MODULE_ENTRY_10
, get_defined_vars(), __DIR__)
MODULE_ENTRY_11
, get_defined_vars(), __DIR__) === $root);

try {
    $module->addDependency($root);
} catch (ValueError $error) {
    echo $error->getMessage(), "\n";
}
?>
--EXPECT--
int(5)
bool(true)
bool(true)
bool(true)
bool(true)
bool(false)
bool(true)
bool(false)
bool(true)
bool(true)
RuntimeModule::addDependency(): Argument #1 ($dependency) must not be the root context
