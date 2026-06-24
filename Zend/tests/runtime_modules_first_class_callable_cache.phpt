--TEST--
Runtime modules: first-class callable caches do not cross module contexts
--FILE--
<?php
foreach (['A', 'B'] as $module) {
    module_run($module, fn() => eval(
        'function phpt_first_class_callable_target(): string { return '
        . var_export($module, true) . '; }'
    ));
}

$factory = fn() => phpt_first_class_callable_target(...);
$a = module_run('A', $factory);
$b = module_run('B', $factory);

var_dump($a());
var_dump($b());
?>
--EXPECT--
string(1) "A"
string(1) "B"
