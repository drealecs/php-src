--TEST--
Runtime modules: stateless closure caching does not reuse another module's closure
--FILE--
<?php
const STATELESS_LABEL = 'root';
function stateless_factory(): Closure {
    return static fn() => STATELESS_LABEL;
}
foreach (['A', 'B'] as $module) {
    module_run($module, static function () use ($module): void {
        define('STATELESS_LABEL', $module);
    });
}
$root = stateless_factory();
echo $root(), "\n";
foreach (['A', 'B', 'A'] as $module) {
    $closure = module_run($module, stateless_factory(...));
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
