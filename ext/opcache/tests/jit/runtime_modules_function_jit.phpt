--TEST--
JIT runtime modules: function JIT falls back for module-sensitive contexts
--INI--
opcache.enable=1
opcache.enable_cli=1
opcache.file_update_protection=0
opcache.jit=function
opcache.jit_buffer_size=16M
--EXTENSIONS--
opcache
--FILE--
<?php
const PHPT_FUNCTION_JIT_ROOT_CONSTANT = 'root';

function phpt_function_jit_continuation(): string {
    new stdClass();
    return PHPT_FUNCTION_JIT_ROOT_CONSTANT;
}

function phpt_function_jit_recv_entry(mixed $unused = null): string {
    return PHPT_FUNCTION_JIT_ROOT_CONSTANT;
}

module_run('A', static function () {
    function phpt_function_jit_module_function(): string {
        return 'A';
    }
    define('PHPT_FUNCTION_JIT_MODULE_CONSTANT', 'constant');
    define('PHPT_FUNCTION_JIT_ROOT_CONSTANT', 'module');
});

$callback = static fn() => [
    phpt_function_jit_module_function(),
    PHPT_FUNCTION_JIT_MODULE_CONSTANT,
];

var_dump(module_run('A', $callback));
var_dump(module_run('A', phpt_function_jit_continuation(...)));
var_dump(module_run('A', phpt_function_jit_recv_entry(...)));

module_run('dependency', static function () {
    eval(<<<'PHP'
        function phpt_function_jit_dependency_function(): string {
            return 'dependency';
        }
        const PHPT_FUNCTION_JIT_DEPENDENCY_CONSTANT = 'visible';
    PHP);
});
module_add_dependency('dependency');

var_dump(phpt_function_jit_dependency_function());
var_dump(PHPT_FUNCTION_JIT_DEPENDENCY_CONSTANT);
?>
--EXPECT--
array(2) {
  [0]=>
  string(1) "A"
  [1]=>
  string(8) "constant"
}
string(6) "module"
string(6) "module"
string(10) "dependency"
string(7) "visible"
