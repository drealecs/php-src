--TEST--
Runtime modules: named class declaration templates are cloned per module
--FILE--
<?php
$a = 'phpt_class_clone_a';
$b = 'phpt_class_clone_b';
module_run($a, fn() => define('PHPT_CLASS_CLONE_VALUE', 'A'));
module_run($b, fn() => define('PHPT_CLASS_CLONE_VALUE', 'B'));

$declare = function (): void {
    class PhptClassCloneThing {
        public static int $counter = 0;
        public function id(): string {
            return PHPT_CLASS_CLONE_VALUE;
        }
        public static function inc(): int {
            return ++self::$counter;
        }
    }
};

module_run($a, $declare);
module_run($b, $declare);
var_dump(module_run($a, fn() => [(new PhptClassCloneThing())->id(), PhptClassCloneThing::inc(), PhptClassCloneThing::inc()]));
var_dump(module_run($b, fn() => [(new PhptClassCloneThing())->id(), PhptClassCloneThing::inc()]));

$inheritA = 'phpt_class_clone_inherit_a';
$inheritB = 'phpt_class_clone_inherit_b';
module_run($inheritA, fn() => eval('const PHPT_CLASS_CLONE_SUFFIX = "A"; class PhptClassCloneBase { public function id(): string { return "base-"; } }'));
module_run($inheritB, fn() => eval('const PHPT_CLASS_CLONE_SUFFIX = "B"; class PhptClassCloneBase { public function id(): string { return "base-"; } }'));

$declareChild = function (): void {
    class PhptClassCloneChild extends PhptClassCloneBase {
        public function id(): string {
            return parent::id() . PHPT_CLASS_CLONE_SUFFIX;
        }
    }
};

module_run($inheritA, $declareChild);
module_run($inheritB, $declareChild);
var_dump(module_run($inheritA, fn() => (new PhptClassCloneChild())->id()));
var_dump(module_run($inheritB, fn() => (new PhptClassCloneChild())->id()));

$templateOwner = 'phpt_class_clone_template_owner';
$target = 'phpt_class_clone_target';
module_run($target, fn() => define('PHPT_CLASS_CLONE_OWNED_VALUE', 'target'));
$ownedTemplate = module_run($templateOwner, fn() => eval('return function (): void { class PhptClassCloneOwnedTemplate { public static function id(): string { return PHPT_CLASS_CLONE_OWNED_VALUE; } } };'));
module_run($target, $ownedTemplate);
var_dump(module_run($target, fn() => PhptClassCloneOwnedTemplate::id()));

$anonA = 'phpt_class_clone_anon_a';
$anonB = 'phpt_class_clone_anon_b';
module_run($anonA, fn() => define('PHPT_CLASS_CLONE_ANON_VALUE', 'anon-A'));
module_run($anonB, fn() => define('PHPT_CLASS_CLONE_ANON_VALUE', 'anon-B'));
$anonFactory = fn() => new class {
    public function id(): string {
        return PHPT_CLASS_CLONE_ANON_VALUE;
    }
};
var_dump(module_run($anonA, $anonFactory)->id());
var_dump(module_run($anonB, $anonFactory)->id());

$anonDependency = 'phpt_class_clone_anon_dependency';
$anonDependant = 'phpt_class_clone_anon_dependant';
module_run($anonDependant, fn() => module_add_dependency($anonDependency));
$dependencyAnonFactory = fn() => new class {};
module_run($anonDependency, $dependencyAnonFactory);
module_run($anonDependant, $dependencyAnonFactory);
echo "anonymous dependency clones\n";

$lateAnonDependency = 'phpt_class_clone_late_anon_dependency';
$lateAnonDependant = 'phpt_class_clone_late_anon_dependant';
$dependencyAnon = module_run($lateAnonDependency, $dependencyAnonFactory);
module_run($lateAnonDependant, fn() => module_add_dependency($lateAnonDependency));
$dependantAnon = module_run($lateAnonDependant, $dependencyAnonFactory);
var_dump(!(new ReflectionClass($dependencyAnon))->isInstance($dependantAnon));

$anonOwner = 'phpt_class_clone_anon_owner';
$anonTarget = 'phpt_class_clone_anon_target';
module_run($anonTarget, fn() => define('PHPT_CLASS_CLONE_OWNED_ANON_VALUE', 'owned-anon'));
$ownedAnonFactory = module_run($anonOwner, fn() => eval('return fn() => new class { public function id(): string { return PHPT_CLASS_CLONE_OWNED_ANON_VALUE; } };'));
var_dump(module_run($anonTarget, $ownedAnonFactory)->id());
?>
--EXPECT--
array(3) {
  [0]=>
  string(1) "A"
  [1]=>
  int(1)
  [2]=>
  int(2)
}
array(2) {
  [0]=>
  string(1) "B"
  [1]=>
  int(1)
}
string(6) "base-A"
string(6) "base-B"
string(6) "target"
string(6) "anon-A"
string(6) "anon-B"
anonymous dependency clones
bool(true)
string(10) "owned-anon"
