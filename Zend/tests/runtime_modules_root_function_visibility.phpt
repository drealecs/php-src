--TEST--
Runtime modules: top-level functions are visible regardless of declaration order
--FILE--
<?php
require_once __DIR__ . '/runtime_modules/file_fixture.inc';

function phpt_root_visibility_first(): string {
    return phpt_root_visibility_second();
}

function phpt_root_visibility_second(): string {
    return 'root';
}

$module = 'phpt_root_function_visibility_module';
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$module] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_298'
function (): void {
    eval(<<<'PHP'
        function phpt_module_visibility_first(): string {
            return phpt_module_visibility_second();
        }

        function phpt_module_visibility_second(): string {
            return 'module';
        }
    PHP);
}
MODULE_ENTRY_298
, get_defined_vars(), __DIR__);

var_dump(phpt_root_visibility_first());
var_dump(($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$module] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_299'
fn() => phpt_module_visibility_first()
MODULE_ENTRY_299
, get_defined_vars(), __DIR__));
var_dump(function_exists('phpt_module_visibility_first'));
?>
--EXPECT--
string(4) "root"
string(6) "module"
bool(false)
