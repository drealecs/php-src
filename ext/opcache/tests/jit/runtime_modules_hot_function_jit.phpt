--TEST--
JIT runtime modules: hot loop OSR falls back for module-sensitive contexts
--INI--
opcache.enable=1
opcache.enable_cli=1
opcache.file_update_protection=0
opcache.jit=1235
opcache.jit_buffer_size=16M
opcache.jit_hot_func=0
opcache.jit_hot_loop=1
--EXTENSIONS--
opcache
--FILE--
<?php
require_once __DIR__ . '/../../../../Zend/tests/runtime_modules/file_fixture.inc';

const PHPT_HOT_FUNCTION_JIT_CONSTANT = 'root';

function phpt_hot_function_jit_probe(int $iterations = 3): string {
    $sum = 0;
    for ($i = 0; $i < $iterations; $i++) {
        $sum += $i;
    }
    return PHPT_HOT_FUNCTION_JIT_CONSTANT . ":$sum";
}

($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['hot-function-jit'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_390'
static function (): void {
    define('PHPT_HOT_FUNCTION_JIT_CONSTANT', 'module');
}
MODULE_ENTRY_390
, get_defined_vars(), __DIR__);

var_dump(($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['hot-function-jit'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_391'
function(int $iterations = 3): string {
    $sum = 0;
    for ($i = 0; $i < $iterations; $i++) {
        $sum += $i;
    }
    return PHPT_HOT_FUNCTION_JIT_CONSTANT . ":$sum";
}
MODULE_ENTRY_391
, get_defined_vars(), __DIR__));
var_dump(phpt_hot_function_jit_probe());
?>
--EXPECT--
string(8) "module:3"
string(6) "root:3"
