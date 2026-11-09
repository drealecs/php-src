--TEST--
JIT runtime modules: hot tracing exits when a method changes root dependencies
--EXTENSIONS--
opcache
--INI--
opcache.enable_cli=1
opcache.file_update_protection=0
opcache.jit=1255
opcache.jit_buffer_size=16M
opcache.jit_hot_func=1
opcache.jit_hot_loop=1
opcache.jit_hot_side_exit=1
--FILE--
<?php
require_once __DIR__ . '/../../../../Zend/tests/runtime_modules/file_fixture.inc';

function tracingImport(RuntimeModule $root, RuntimeModule $dependency, bool $add): bool {
    if ($add) {
        $root->addDependency($dependency);
    }
    return defined('TRACING_IMPORTED');
}
$root = RuntimeModule::current();
$dependency = new RuntimeModule();
($GLOBALS["module_fixture"])($dependency, <<<'MODULE_ENTRY_384'
static fn() => define('TRACING_IMPORTED', true)
MODULE_ENTRY_384
, get_defined_vars(), __DIR__);
for ($i = 0; $i < 10; $i++) {
    if (tracingImport($root, $dependency, false)) {
        echo "unexpected visibility\n";
    }
}
var_dump(tracingImport($root, $dependency, true));
var_dump(tracingImport($root, $dependency, false));
?>
--EXPECT--
bool(true)
bool(true)
