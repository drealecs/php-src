--TEST--
Runtime modules: autoload recursion guard is per module
--FILE--
<?php
$events = [];
$a = 'phpt_autoload_guard_a';
$b = 'phpt_autoload_guard_b';

module_run($b, function () use (&$events): void {
    spl_autoload_register(function (string $class) use (&$events): void {
        $events[] = "B:$class";
        if ($class === 'PhptAutoloadGuardThing') {
            $events[] = 'B-recursive=' . (class_exists($class) ? 'true' : 'false');
            eval('class PhptAutoloadGuardThing { public static function id(): string { return "B"; } }');
        }
    });
});

module_run($a, function () use (&$events, $b): void {
    spl_autoload_register(function (string $class) use (&$events, $b): void {
        $events[] = "A:$class";
        if ($class === 'PhptAutoloadGuardThing') {
            $events[] = 'A-recursive=' . (class_exists($class) ? 'true' : 'false');
            $events[] = 'B-visible=' . (module_run($b, fn() => class_exists($class)) ? 'true' : 'false');
            eval('class PhptAutoloadGuardThing { public static function id(): string { return "A"; } }');
        }
    });
});

var_dump(module_run($a, fn() => class_exists('PhptAutoloadGuardThing')));
var_dump(module_run($a, fn() => PhptAutoloadGuardThing::id()));
var_dump(module_run($b, fn() => PhptAutoloadGuardThing::id()));
echo implode("\n", $events), "\n";
?>
--EXPECT--
bool(true)
string(1) "A"
string(1) "B"
A:PhptAutoloadGuardThing
A-recursive=false
B:PhptAutoloadGuardThing
B-recursive=false
B-visible=true
