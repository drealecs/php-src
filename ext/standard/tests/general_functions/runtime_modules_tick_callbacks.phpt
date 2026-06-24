--TEST--
Runtime modules: internal tick callbacks execute in their registration module
--FILE--
<?php
require_once __DIR__ . '/../../../../Zend/tests/runtime_modules/file_fixture.inc';

($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['tick-owner'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_438'
static function (): void {
    register_tick_function('spl_autoload_register');
}
MODULE_ENTRY_438
, get_defined_vars(), __DIR__);
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['tick-trigger'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_439'
static fn() => eval('declare(ticks=1); $value = 1;')
MODULE_ENTRY_439
, get_defined_vars(), __DIR__);
unregister_tick_function('spl_autoload_register');
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['tick-owner'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_440'
static function (): void {
    var_dump(spl_autoload_functions());
    spl_autoload_unregister('spl_autoload');
}
MODULE_ENTRY_440
, get_defined_vars(), __DIR__);
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['tick-trigger'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_441'
static fn() => var_dump(spl_autoload_functions())
MODULE_ENTRY_441
, get_defined_vars(), __DIR__);
var_dump(spl_autoload_functions());

register_tick_function('spl_autoload_register');
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['tick-trigger'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_442'
static fn() => eval('declare(ticks=1); $value = 1;')
MODULE_ENTRY_442
, get_defined_vars(), __DIR__);
unregister_tick_function('spl_autoload_register');
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['tick-trigger'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_443'
static fn() => var_dump(spl_autoload_functions())
MODULE_ENTRY_443
, get_defined_vars(), __DIR__);
var_dump(spl_autoload_functions());
?>
--EXPECT--
array(1) {
  [0]=>
  string(12) "spl_autoload"
}
array(0) {
}
array(0) {
}
array(0) {
}
array(1) {
  [0]=>
  string(12) "spl_autoload"
}
