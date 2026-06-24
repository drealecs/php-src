--TEST--
Runtime modules: API basics
--FILE--
<?php
$m = 'phpt_api';
$a = 2;
$b = 3;
var_dump(module_run($m, fn() => $a + $b));
module_run($m, fn() => eval('class PhptApiImplicitReuse {}'));
var_dump(module_run($m, fn() => class_exists('PhptApiImplicitReuse', false)));
var_dump(module_run('phpt_api_other', fn() => class_exists('PhptApiImplicitReuse', false)));

$main = 'phpt_api_main';
$dep = 'phpt_api_dep';
module_run($main, fn() => module_add_dependency($dep));
module_run($dep, fn() => eval('class PhptApiDependency {}'));
var_dump(module_run($main, fn() => class_exists('PhptApiDependency', false)));

$rootDep = 'phpt_api_root_dep';
module_run($rootDep, fn() => eval('class PhptApiRootDependency {}'));
var_dump(class_exists('PhptApiRootDependency', false));
module_add_dependency($rootDep);
var_dump(class_exists('PhptApiRootDependency', false));

try {
    module_run('', fn() => null);
} catch (ValueError $e) {
    echo $e->getMessage(), "\n";
}

try {
    module_add_dependency('');
} catch (ValueError $e) {
    echo $e->getMessage(), "\n";
}
?>
--EXPECT--
int(5)
bool(true)
bool(false)
bool(true)
bool(false)
bool(true)
module_run(): Argument #1 ($module) must not be empty
module_add_dependency(): Argument #1 ($dependency) must not be empty
