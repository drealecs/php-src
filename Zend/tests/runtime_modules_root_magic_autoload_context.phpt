--TEST--
Runtime modules: magic autoloaders retain an explicit root context
--FILE--
<?php
function phpt_runtime_module_root_marker(): void {}

$loader = module_run('phpt_runtime_module_magic_owner', static fn() => new class {
    public function __call(string $name, array $arguments): void
    {
        var_dump(function_exists('phpt_runtime_module_root_marker'));
    }
});

spl_autoload_register([$loader, 'load']);
class_exists('PhptRuntimeModuleMagicMissing');
?>
--EXPECT--
bool(true)
