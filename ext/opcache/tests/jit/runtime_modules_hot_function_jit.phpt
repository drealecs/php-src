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
const PHPT_HOT_FUNCTION_JIT_CONSTANT = 'root';

function phpt_hot_function_jit_probe(int $iterations = 3): string {
    $sum = 0;
    for ($i = 0; $i < $iterations; $i++) {
        $sum += $i;
    }
    return PHPT_HOT_FUNCTION_JIT_CONSTANT . ":$sum";
}

module_run('hot-function-jit', static function (): void {
    define('PHPT_HOT_FUNCTION_JIT_CONSTANT', 'module');
});

var_dump(module_run('hot-function-jit', phpt_hot_function_jit_probe(...)));
var_dump(phpt_hot_function_jit_probe());
?>
--EXPECT--
string(8) "module:3"
string(6) "root:3"
