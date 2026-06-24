--TEST--
Runtime modules: dependency collision checks include existing dependencies and dependants
--FILE--
<?php
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
module_run($depA, fn() => eval('class PhptDependencyCollisionThing {}'));
module_run($depB, fn() => eval('class PhptDependencyCollisionThing {}'));
module_run($main, fn() => module_add_dependency($depA));
phpt_dependency_collision_error(fn() => module_run($main, fn() => module_add_dependency($depB)));

$owner = 'phpt_dep_collision_owner';
$visible = 'phpt_dep_collision_visible';
module_run($owner, fn() => module_add_dependency($visible));
module_run($visible, fn() => define('PHPT_DEPENDENCY_LATE_DECLARATION', 1));
phpt_dependency_collision_error(
    fn() => module_run($owner, fn() => define('PHPT_DEPENDENCY_LATE_DECLARATION', 2))
);

$dependant = 'phpt_dep_collision_dependant';
$existingDep = 'phpt_dep_collision_existing_dep';
$lateDep = 'phpt_dep_collision_late_dep';
module_run($dependant, fn() => module_add_dependency($existingDep));
module_run($dependant, fn() => module_add_dependency($lateDep));
module_run($existingDep, fn() => define('PHPT_DEPENDENCY_DEPENDANT_COLLISION', 1));
phpt_dependency_collision_error(
    fn() => module_run($lateDep, fn() => define('PHPT_DEPENDENCY_DEPENDANT_COLLISION', 2))
);

class PhptRootDependencyCollisionThing {}
$rootConflict = 'phpt_dep_collision_root_conflict';
module_run($rootConflict, fn() => eval('class PhptRootDependencyCollisionThing {}'));
phpt_dependency_collision_error(fn() => module_add_dependency($rootConflict));

$rootLate = 'phpt_dep_collision_root_late';
module_add_dependency($rootLate);
define('PHPT_DEPENDENCY_ROOT_LATE_COLLISION', 1);
phpt_dependency_collision_error(
    fn() => module_run($rootLate, fn() => define('PHPT_DEPENDENCY_ROOT_LATE_COLLISION', 2))
);
?>
--EXPECTF--
Cannot add dependency "phpt_dep_collision_dep_b" to runtime module "phpt_dep_collision_main": class name "phptdependencycollisionthing" conflicts with runtime module "phpt_dep_collision_dep_a"
Cannot declare constant PHPT_DEPENDENCY_LATE_DECLARATION in runtime module "phpt_dep_collision_owner": name conflicts with runtime module "phpt_dep_collision_visible"
Cannot declare constant PHPT_DEPENDENCY_DEPENDANT_COLLISION in runtime module "phpt_dep_collision_late_dep": name conflicts with runtime module "phpt_dep_collision_existing_dep"
Cannot add dependency "phpt_dep_collision_root_conflict" to the root context: class name "phptrootdependencycollisionthing" conflicts with the root context
Cannot declare constant PHPT_DEPENDENCY_ROOT_LATE_COLLISION in runtime module "phpt_dep_collision_root_late": name conflicts with the root context
