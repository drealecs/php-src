--TEST--
JIT runtime modules: dependency methods invalidate root assumptions after a call
--EXTENSIONS--
opcache
--INI--
opcache.enable_cli=1
opcache.file_update_protection=0
opcache.jit=function
opcache.jit_buffer_size=16M
--FILE--
<?php
require_once __DIR__ . '/../../../../Zend/tests/runtime_modules/file_fixture.inc';

function rootImport(RuntimeModule $target, RuntimeModule $dependency): array {
    $before = defined('IMPORTED_VALUE');
    $target->addDependency($dependency);
    return [$before, defined('IMPORTED_VALUE'), IMPORTED_VALUE, imported_value()];
}
$dependency = new RuntimeModule();
($GLOBALS["module_fixture"])($dependency, <<<'MODULE_ENTRY_383'
static fn() => eval('const IMPORTED_VALUE = "visible"; function imported_value() { return IMPORTED_VALUE; }')
MODULE_ENTRY_383
, get_defined_vars(), __DIR__);
var_dump(rootImport(RuntimeModule::current(), $dependency));
?>
--EXPECT--
array(4) {
  [0]=>
  bool(false)
  [1]=>
  bool(true)
  [2]=>
  string(7) "visible"
  [3]=>
  string(7) "visible"
}
