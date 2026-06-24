--TEST--
JIT runtime modules: hot symbol lookups remain module-local
--INI--
opcache.enable=1
opcache.enable_cli=1
opcache.file_update_protection=0
opcache.jit=tracing
opcache.jit_buffer_size=16M
opcache.jit_hot_func=1
opcache.jit_hot_loop=1
--EXTENSIONS--
opcache
--FILE--
<?php
$a = 'phpt_jit_hot_symbols_a';
$b = 'phpt_jit_hot_symbols_b';

module_run($a, function () {
    eval('function phpt_jit_hot_symbol_value() { return PHP_JIT_HOT_SYMBOL_CONST; } const PHP_JIT_HOT_SYMBOL_CONST = "A";');
});
module_run($b, function () {
    eval('function phpt_jit_hot_symbol_value() { return PHP_JIT_HOT_SYMBOL_CONST; } const PHP_JIT_HOT_SYMBOL_CONST = "B";');
});

$callback = function (): string {
    $last = '';
    for ($i = 0; $i < 1000; $i++) {
        $last = phpt_jit_hot_symbol_value();
    }
    return $last . ':' . (defined('PHP_JIT_HOT_SYMBOL_CONST') ? PHP_JIT_HOT_SYMBOL_CONST : 'missing');
};

var_dump(module_run($a, $callback));
var_dump(module_run($b, $callback));
var_dump(module_run($a, $callback));
?>
--EXPECT--
string(3) "A:A"
string(3) "B:B"
string(3) "A:A"
