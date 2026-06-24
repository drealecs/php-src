--TEST--
Runtime modules: module-owned SPL autoloaders
--FILE--
<?php
$m1 = 'phpt_autoload_m1';
$m2 = 'phpt_autoload_m2';
module_run($m1, function () {
    spl_autoload_register(function (string $class): void {
        if ($class === 'PhptAutoloadThing') {
            eval('class PhptAutoloadThing { function id() { return 1; } }');
        }
    });
});
module_run($m2, function () {
    spl_autoload_register(function (string $class): void {
        if ($class === 'PhptAutoloadThing') {
            eval('class PhptAutoloadThing { function id() { return 2; } }');
        }
    });
});
var_dump(count(spl_autoload_functions()));
var_dump(count(module_run($m1, fn() => spl_autoload_functions())));
var_dump(count(module_run($m2, fn() => spl_autoload_functions())));
var_dump(module_run($m1, fn() => (new PhptAutoloadThing())->id()));
var_dump(module_run($m2, fn() => (new PhptAutoloadThing())->id()));
?>
--EXPECT--
int(0)
int(1)
int(1)
int(1)
int(2)
