--TEST--
Runtime modules: top-level functions are visible regardless of declaration order
--FILE--
<?php
function phpt_root_visibility_first(): string {
    return phpt_root_visibility_second();
}

function phpt_root_visibility_second(): string {
    return 'root';
}

$module = 'phpt_root_function_visibility_module';
module_run($module, function (): void {
    eval(<<<'PHP'
        function phpt_module_visibility_first(): string {
            return phpt_module_visibility_second();
        }

        function phpt_module_visibility_second(): string {
            return 'module';
        }
    PHP);
});

var_dump(phpt_root_visibility_first());
var_dump(module_run($module, fn() => phpt_module_visibility_first()));
var_dump(function_exists('phpt_module_visibility_first'));
?>
--EXPECT--
string(4) "root"
string(6) "module"
bool(false)
