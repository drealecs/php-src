--TEST--
Runtime modules: explicit call context survives reentrant call setup
--FILE--
<?php
require_once __DIR__ . '/runtime_modules/file_fixture.inc';


($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['A'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_13'
static function () {
    class PhptCallContextA {}
}
MODULE_ENTRY_13
, get_defined_vars(), __DIR__);

set_error_handler(static function () {
    echo "handler: ";
    var_dump(class_exists('PhptCallContextA', false));
    return true;
});

($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['A'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_14'
#[\Deprecated('trigger reentrant call setup')] static function () {
    echo "callback: ";
    var_dump(class_exists('PhptCallContextA', false));
}
MODULE_ENTRY_14
, get_defined_vars(), __DIR__);

restore_error_handler();

?>
--EXPECT--
handler: bool(false)
callback: bool(true)
