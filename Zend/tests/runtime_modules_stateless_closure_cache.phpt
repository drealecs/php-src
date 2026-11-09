--TEST--
Runtime modules: stateless closure caching does not reuse another module's closure
--FILE--
<?php
require_once __DIR__ . '/runtime_modules/file_fixture.inc';

const STATELESS_LABEL = 'root';
function stateless_factory(): Closure {
    return static fn() => STATELESS_LABEL;
}
$factories = [];
foreach (['A', 'B'] as $module) {
    ($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$module] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_319'
static function () use ($module): void {
        define('STATELESS_LABEL', $module);
    }
MODULE_ENTRY_319
, get_defined_vars(), __DIR__);
    $factories[$module] = ($GLOBALS['module_fixture'])($GLOBALS['runtime_modules'][$module],
        'fn() => static fn() => static fn() => STATELESS_LABEL', [], __DIR__);
}
$root = stateless_factory();
echo $root(), "\n";
foreach (['A', 'B', 'A'] as $module) {
    $closure = $factories[$module]();
    echo $closure(), "\n";
}
echo stateless_factory()(), "\n";
var_dump(stateless_factory() === $root);
?>
--EXPECT--
root
A
B
A
root
bool(true)
