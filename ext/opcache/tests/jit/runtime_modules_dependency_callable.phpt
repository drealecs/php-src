--TEST--
JIT runtime modules: a dependency method invoked through a closure changes root visibility
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

function importCallback(Closure $callback, RuntimeModule $dependency): string {
    $callback($dependency);
    return IMPORTED_CALLBACK_VALUE;
}
$dependency = new RuntimeModule();
($GLOBALS["module_fixture"])($dependency, <<<'MODULE_ENTRY_382'
static fn() => define('IMPORTED_CALLBACK_VALUE', 'visible')
MODULE_ENTRY_382
, get_defined_vars(), __DIR__);
var_dump(importCallback(RuntimeModule::current()->addDependency(...), $dependency));
?>
--EXPECT--
string(7) "visible"
