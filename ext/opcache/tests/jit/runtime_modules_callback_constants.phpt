--TEST--
JIT runtime modules: callbacks invalidate negative constant lookups in the active native frame
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

function callbackConstants(): array {
    $seen = [];
    for ($i = 0; $i < 3; $i++) {
        $seen[] = defined('CALLBACK_IMPORTED_CONSTANT');
        if ($i === 1) {
            trigger_error('import', E_USER_NOTICE);
        }
    }
    return [$seen, CALLBACK_IMPORTED_CONSTANT];
}
$root = RuntimeModule::current();
$dependency = new RuntimeModule();
($GLOBALS["module_fixture"])($dependency, <<<'MODULE_ENTRY_379'
static fn() => define('CALLBACK_IMPORTED_CONSTANT', 'visible')
MODULE_ENTRY_379
, get_defined_vars(), __DIR__);
set_error_handler(static function () use ($root, $dependency): bool {
    $root->addDependency($dependency);
    return true;
});
var_dump(callbackConstants());
restore_error_handler();
?>
--EXPECT--
array(2) {
  [0]=>
  array(3) {
    [0]=>
    bool(false)
    [1]=>
    bool(false)
    [2]=>
    bool(true)
  }
  [1]=>
  string(7) "visible"
}
