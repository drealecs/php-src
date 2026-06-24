--TEST--
Runtime modules: direct dependency autoloaders are visible to dependant modules
--FILE--
<?php
$dependency = 'phpt_dep_autoload_dependency';
$main = 'phpt_dep_autoload_main';

module_run($main, fn() => module_add_dependency($dependency));

module_run($dependency, function (): void {
    spl_autoload_register(function (string $class): void {
        if ($class === 'PhptDependencyAutoloadThing') {
            eval('class PhptDependencyAutoloadThing { public static function id(): string { return "dependency"; } }');
        }
    });
});

var_dump(module_run($main, fn() => class_exists('PhptDependencyAutoloadThing')));
var_dump(module_run($main, fn() => PhptDependencyAutoloadThing::id()));
var_dump(class_exists('PhptDependencyAutoloadThing', false));
?>
--EXPECT--
bool(true)
string(10) "dependency"
bool(false)
