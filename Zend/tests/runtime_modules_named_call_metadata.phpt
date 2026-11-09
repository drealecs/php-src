--TEST--
Runtime modules: named calls map arguments using the runtime-resolved symbol
--FILE--
<?php
require_once __DIR__ . '/runtime_modules/file_fixture.inc';

function phpt_named_call_metadata($root): string {
    return 'root';
}

($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['phpt_named_call_metadata'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_219'
static function (): void {
    eval(<<<'PHP'
        function phpt_named_call_metadata($module): string {
            return 'module';
        }
    PHP);
}
MODULE_ENTRY_219
, get_defined_vars(), __DIR__);

try {
    ($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['phpt_named_call_metadata'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_220'
fn() => phpt_named_call_metadata(root: 'value')
MODULE_ENTRY_220
, get_defined_vars(), __DIR__);
} catch (Error $e) {
    echo $e->getMessage(), "\n";
}
?>
--EXPECT--
Unknown named parameter $root
