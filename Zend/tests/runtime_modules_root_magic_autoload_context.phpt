--TEST--
Runtime modules: magic user autoloaders retain their owner when registered in root
--FILE--
<?php
require_once __DIR__ . '/runtime_modules/file_fixture.inc';

function phpt_runtime_module_root_marker(): void {}

$loader = ($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['phpt_runtime_module_magic_owner'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_304'
static fn() => new class {
    public function __call(string $name, array $arguments): void
    {
        var_dump(function_exists('phpt_runtime_module_root_marker'));
        var_dump(RuntimeModule::current() === $GLOBALS['runtime_modules']['phpt_runtime_module_magic_owner']);
    }
}
MODULE_ENTRY_304
, get_defined_vars(), __DIR__);

spl_autoload_register([$loader, 'load']);
class_exists('PhptRuntimeModuleMagicMissing');
?>
--EXPECT--
bool(false)
bool(true)
