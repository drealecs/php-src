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
require_once __DIR__ . '/../../../../Zend/tests/runtime_modules/file_fixture.inc';

const PHPT_FUNCTION_JIT_ROOT_CONSTANT = 'root';

function phpt_function_jit_continuation(): string {
    new stdClass();
    return PHPT_FUNCTION_JIT_ROOT_CONSTANT;
}

function phpt_function_jit_recv_entry(mixed $unused = null): string {
    return PHPT_FUNCTION_JIT_ROOT_CONSTANT;
}

($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['A'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_385'
static function () {
    function phpt_function_jit_module_function(): string {
        return 'A';
    }
    define('PHPT_FUNCTION_JIT_MODULE_CONSTANT', 'constant');
    define('PHPT_FUNCTION_JIT_ROOT_CONSTANT', 'module');
}
MODULE_ENTRY_385
, get_defined_vars(), __DIR__);

var_dump(($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['A'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_386'
static fn() => [
    phpt_function_jit_module_function(),
    PHPT_FUNCTION_JIT_MODULE_CONSTANT,
]
MODULE_ENTRY_386
, get_defined_vars(), __DIR__));
var_dump(($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['A'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_387'
function(): string {
    new stdClass();
    return PHPT_FUNCTION_JIT_ROOT_CONSTANT;
}
MODULE_ENTRY_387
, get_defined_vars(), __DIR__));
var_dump(($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['A'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_388'
function(mixed $unused = null): string {
    return PHPT_FUNCTION_JIT_ROOT_CONSTANT;
}
MODULE_ENTRY_388
, get_defined_vars(), __DIR__));

($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['dependency'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_389'
static function () {
    eval(<<<'PHP'
        function phpt_function_jit_dependency_function(): string {
            return 'dependency';
        }
        const PHPT_FUNCTION_JIT_DEPENDENCY_CONSTANT = 'visible';
    PHP);
}
MODULE_ENTRY_389
, get_defined_vars(), __DIR__);
\RuntimeModule::current()->addDependency($GLOBALS["runtime_modules"]['dependency'] ??= new \RuntimeModule());

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
