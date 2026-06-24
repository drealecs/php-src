--TEST--
Runtime modules: dependency collision checks include existing dependencies and dependants
--FILE--
<?php
require_once __DIR__ . '/runtime_modules/file_fixture.inc';

function phpt_dependency_collision_error(Closure $callback): void {
    try {
        $callback();
    } catch (Throwable $e) {
        echo $e->getMessage(), "\n";
    }
}

$main = 'phpt_dep_collision_main';
$depA = 'phpt_dep_collision_dep_a';
$depB = 'phpt_dep_collision_dep_b';
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$depA] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_121'
fn() => eval('class PhptDependencyCollisionThing {}')
MODULE_ENTRY_121
, get_defined_vars(), __DIR__);
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$depB] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_122'
fn() => eval('class PhptDependencyCollisionThing {}')
MODULE_ENTRY_122
, get_defined_vars(), __DIR__);
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$main] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_123'
fn() => \RuntimeModule::current()->addDependency($GLOBALS["runtime_modules"][$depA] ??= new \RuntimeModule())
MODULE_ENTRY_123
, get_defined_vars(), __DIR__);
phpt_dependency_collision_error(fn() => ($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$main] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_124'
fn() => \RuntimeModule::current()->addDependency($GLOBALS["runtime_modules"][$depB] ??= new \RuntimeModule())
MODULE_ENTRY_124
, ['depB' => $depB], __DIR__));

$owner = 'phpt_dep_collision_owner';
$visible = 'phpt_dep_collision_visible';
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$owner] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_125'
fn() => \RuntimeModule::current()->addDependency($GLOBALS["runtime_modules"][$visible] ??= new \RuntimeModule())
MODULE_ENTRY_125
, get_defined_vars(), __DIR__);
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$visible] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_126'
fn() => define('PHPT_DEPENDENCY_LATE_DECLARATION', 1)
MODULE_ENTRY_126
, get_defined_vars(), __DIR__);
phpt_dependency_collision_error(
    fn() => ($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$owner] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_127'
fn() => define('PHPT_DEPENDENCY_LATE_DECLARATION', 2)
MODULE_ENTRY_127
, get_defined_vars(), __DIR__)
);

$dependant = 'phpt_dep_collision_dependant';
$existingDep = 'phpt_dep_collision_existing_dep';
$lateDep = 'phpt_dep_collision_late_dep';
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$dependant] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_128'
fn() => \RuntimeModule::current()->addDependency($GLOBALS["runtime_modules"][$existingDep] ??= new \RuntimeModule())
MODULE_ENTRY_128
, get_defined_vars(), __DIR__);
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$dependant] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_129'
fn() => \RuntimeModule::current()->addDependency($GLOBALS["runtime_modules"][$lateDep] ??= new \RuntimeModule())
MODULE_ENTRY_129
, get_defined_vars(), __DIR__);
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$existingDep] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_130'
fn() => define('PHPT_DEPENDENCY_DEPENDANT_COLLISION', 1)
MODULE_ENTRY_130
, get_defined_vars(), __DIR__);
phpt_dependency_collision_error(
    fn() => ($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$lateDep] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_131'
fn() => define('PHPT_DEPENDENCY_DEPENDANT_COLLISION', 2)
MODULE_ENTRY_131
, get_defined_vars(), __DIR__)
);

class PhptRootDependencyCollisionThing {}
$rootConflict = 'phpt_dep_collision_root_conflict';
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$rootConflict] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_132'
fn() => eval('class PhptRootDependencyCollisionThing {}')
MODULE_ENTRY_132
, get_defined_vars(), __DIR__);
phpt_dependency_collision_error(fn() => \RuntimeModule::current()->addDependency($GLOBALS["runtime_modules"][$rootConflict] ??= new \RuntimeModule()));

$rootLate = 'phpt_dep_collision_root_late';
\RuntimeModule::current()->addDependency($GLOBALS["runtime_modules"][$rootLate] ??= new \RuntimeModule());
define('PHPT_DEPENDENCY_ROOT_LATE_COLLISION', 1);
phpt_dependency_collision_error(
    fn() => ($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$rootLate] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_133'
fn() => define('PHPT_DEPENDENCY_ROOT_LATE_COLLISION', 2)
MODULE_ENTRY_133
, get_defined_vars(), __DIR__)
);
?>
--EXPECTF--
Cannot add dependency #%d to runtime module #%d: class name "phptdependencycollisionthing" conflicts with runtime module #%d
Cannot declare constant PHPT_DEPENDENCY_LATE_DECLARATION in runtime module "#%d": name conflicts with runtime module "#%d"
Cannot declare constant PHPT_DEPENDENCY_DEPENDANT_COLLISION in runtime module "#%d": name conflicts with runtime module "#%d"
Cannot add dependency #%d to runtime module root: class name "phptrootdependencycollisionthing" conflicts with the root context
Cannot declare constant PHPT_DEPENDENCY_ROOT_LATE_COLLISION in runtime module "#%d": name conflicts with the root context
