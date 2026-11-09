--TEST--
JIT runtime modules: closure call frame preserves runtime module
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
require_once __DIR__ . '/../../../../Zend/tests/runtime_modules/file_fixture.inc';

$module = 'phpt_jit_runtime_module';
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$module] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_377'
fn() => eval('class PhptJitRuntimeModuleThing { public static function id(): string { return "module"; } }')
MODULE_ENTRY_377
, get_defined_vars(), __DIR__);
$closure = ($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$module] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_378'
fn() => fn() => PhptJitRuntimeModuleThing::id()
MODULE_ENTRY_378
, get_defined_vars(), __DIR__);

function phpt_jit_runtime_module_call(Closure $closure): string {
    return $closure();
}

for ($i = 0; $i < 1000; $i++) {
    $last = phpt_jit_runtime_module_call($closure);
}
var_dump($last);
?>
--EXPECT--
string(6) "module"
