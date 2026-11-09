--TEST--
Runtime modules: optimizer does not substitute context-sensitive user calls
--INI--
opcache.enable=1
opcache.enable_cli=1
opcache.file_update_protection=0
opcache.optimization_level=0x7FFEBFFF
opcache.jit=disable
--EXTENSIONS--
opcache
--FILE--
<?php
require_once __DIR__ . '/../../../Zend/tests/runtime_modules/file_fixture.inc';

function phpt_optimizer_call_target(): string {
    return 'root';
}
function phpt_optimizer_call_probe(): string {
    return phpt_optimizer_call_target();
}

($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['phpt_optimizer_calls'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_397'
static function (): void {
    eval(<<<'PHP'
        function phpt_optimizer_call_target(): string {
            return 'module';
        }
    PHP);
}
MODULE_ENTRY_397
, get_defined_vars(), __DIR__);

var_dump(($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['phpt_optimizer_calls'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_398'
function(): string {
    return phpt_optimizer_call_target();
}
MODULE_ENTRY_398
, get_defined_vars(), __DIR__));
?>
--EXPECT--
string(6) "module"
