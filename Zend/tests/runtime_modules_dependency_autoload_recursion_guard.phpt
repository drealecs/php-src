--TEST--
Runtime modules: dependency autoload recursion is guarded in the dependency context
--FILE--
<?php
$events = [];
$a = 'phpt_dependency_autoload_guard_a';
$b = 'phpt_dependency_autoload_guard_b';

module_run($a, fn() => module_add_dependency($b));
module_run($b, function () use (&$events): void {
    spl_autoload_register(function (string $class) use (&$events): void {
        $events[] = $class;
        $events[] = class_exists($class) ? 'recursive=true' : 'recursive=false';
        eval('class PhptDependencyAutoloadGuardThing {}');
    });
});

var_dump(module_run($a, fn() => class_exists('PhptDependencyAutoloadGuardThing')));
var_dump($events);
?>
--EXPECT--
bool(true)
array(2) {
  [0]=>
  string(32) "PhptDependencyAutoloadGuardThing"
  [1]=>
  string(15) "recursive=false"
}
