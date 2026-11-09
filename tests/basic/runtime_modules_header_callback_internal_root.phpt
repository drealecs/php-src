--TEST--
Runtime modules: internal header callbacks retain an explicit root registration
--FILE--
<?php
require_once __DIR__ . '/../../Zend/tests/runtime_modules/file_fixture.inc';

header_register_callback('spl_autoload_register');
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['header-trigger'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_449'
static function (): void {
    echo "headers\n";
    var_dump(spl_autoload_functions());
}
MODULE_ENTRY_449
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
