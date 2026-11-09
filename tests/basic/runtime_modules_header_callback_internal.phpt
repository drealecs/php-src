--TEST--
Runtime modules: internal header callbacks execute in their registration module
--FILE--
<?php
require_once __DIR__ . '/../../Zend/tests/runtime_modules/file_fixture.inc';

($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['header-owner'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_446'
static function (): void {
    header_register_callback('spl_autoload_register');
}
MODULE_ENTRY_446
, get_defined_vars(), __DIR__);
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['header-trigger'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_447'
static function (): void {
    echo "headers\n";
    var_dump(spl_autoload_functions());
}
MODULE_ENTRY_447
, get_defined_vars(), __DIR__);
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['header-owner'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_448'
static function (): void {
    var_dump(spl_autoload_functions());
}
MODULE_ENTRY_448
, get_defined_vars(), __DIR__);
var_dump(spl_autoload_functions());
?>
--EXPECT--
headers
array(0) {
}
array(1) {
  [0]=>
  string(12) "spl_autoload"
}
array(0) {
}
