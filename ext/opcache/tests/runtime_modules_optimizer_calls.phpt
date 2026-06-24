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
function phpt_optimizer_call_target(): string {
    return 'root';
}
function phpt_optimizer_call_probe(): string {
    return phpt_optimizer_call_target();
}

module_run('phpt_optimizer_calls', static function (): void {
    eval(<<<'PHP'
        function phpt_optimizer_call_target(): string {
            return 'module';
        }
    PHP);
});

var_dump(module_run('phpt_optimizer_calls', phpt_optimizer_call_probe(...)));
?>
--EXPECT--
string(6) "module"
