--TEST--
Runtime modules: autoload recursion guard is per module
--FILE--
<?php
require_once __DIR__ . '/runtime_modules/file_fixture.inc';

$events = [];
$a = 'phpt_autoload_guard_a';
$b = 'phpt_autoload_guard_b';

($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$b] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_7'
function () use (&$events): void {
    spl_autoload_register(function (string $class) use (&$events): void {
        $events[] = "B:$class";
        if ($class === 'PhptAutoloadGuardThing') {
            $events[] = 'B-recursive=' . (class_exists($class) ? 'true' : 'false');
            eval('class PhptAutoloadGuardThing { public static function id(): string { return "B"; } }');
        }
    });
}
MODULE_ENTRY_7
, ['events'=>&$events] + get_defined_vars(), __DIR__);

($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$a] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_9'
function () use (&$events, $b): void {
    spl_autoload_register(function (string $class) use (&$events, $b): void {
        $events[] = "A:$class";
        if ($class === 'PhptAutoloadGuardThing') {
            $events[] = 'A-recursive=' . (class_exists($class) ? 'true' : 'false');
            $events[] = 'B-visible=' . (($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$b] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_8'
fn() => class_exists($class)
MODULE_ENTRY_8
, get_defined_vars(), __DIR__) ? 'true' : 'false');
            eval('class PhptAutoloadGuardThing { public static function id(): string { return "A"; } }');
        }
    });
}
MODULE_ENTRY_9
, ['events'=>&$events] + get_defined_vars(), __DIR__);

var_dump(($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$a] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_10'
fn() => class_exists('PhptAutoloadGuardThing')
MODULE_ENTRY_10
, get_defined_vars(), __DIR__));
var_dump(($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$a] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_11'
fn() => PhptAutoloadGuardThing::id()
MODULE_ENTRY_11
, get_defined_vars(), __DIR__));
var_dump(($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$b] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_12'
fn() => PhptAutoloadGuardThing::id()
MODULE_ENTRY_12
, get_defined_vars(), __DIR__));
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
