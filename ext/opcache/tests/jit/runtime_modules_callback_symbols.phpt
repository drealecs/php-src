--TEST--
JIT runtime modules: native warning callbacks expose imported classes and functions
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

function warningCallbackSymbols(): array {
    $value = $undefined + 1;
    $object = new CallbackImportedClass();
    return [$value, CallbackImportedClass::VALUE, $object instanceof CallbackImportedClass, callback_imported_function()];
}
$root = RuntimeModule::current();
$dependency = new RuntimeModule();
($GLOBALS["module_fixture"])($dependency, <<<'MODULE_ENTRY_381'
static fn() => eval(<<<'PHP'
    class CallbackImportedClass { public const VALUE = 'class'; }
    function callback_imported_function(): string { return 'function'; }
PHP)
MODULE_ENTRY_381
, get_defined_vars(), __DIR__);
set_error_handler(static function () use ($root, $dependency): bool {
    $root->addDependency($dependency);
    return true;
});
var_dump(warningCallbackSymbols());
restore_error_handler();
?>
--EXPECT--
array(4) {
  [0]=>
  int(1)
  [1]=>
  string(5) "class"
  [2]=>
  bool(true)
  [3]=>
  string(8) "function"
}
