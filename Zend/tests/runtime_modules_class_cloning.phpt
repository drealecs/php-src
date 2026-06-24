--TEST--
Runtime modules: independently compiled classes isolate state and returned factories retain ownership
--FILE--
<?php
require_once __DIR__ . '/runtime_modules/file_fixture.inc';

$a = 'phpt_class_clone_a';
$b = 'phpt_class_clone_b';
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$a] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_45'
fn() => define('PHPT_CLASS_CLONE_VALUE', 'A')
MODULE_ENTRY_45
, get_defined_vars(), __DIR__);
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$b] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_46'
fn() => define('PHPT_CLASS_CLONE_VALUE', 'B')
MODULE_ENTRY_46
, get_defined_vars(), __DIR__);

($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$a] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_47'
function (): void {
    class PhptClassCloneThing {
        public static int $counter = 0;
        public function id(): string {
            return PHPT_CLASS_CLONE_VALUE;
        }
        public static function inc(): int {
            return ++self::$counter;
        }
    }
}
MODULE_ENTRY_47
, get_defined_vars(), __DIR__);
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$b] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_48'
function (): void {
    class PhptClassCloneThing {
        public static int $counter = 0;
        public function id(): string {
            return PHPT_CLASS_CLONE_VALUE;
        }
        public static function inc(): int {
            return ++self::$counter;
        }
    }
}
MODULE_ENTRY_48
, get_defined_vars(), __DIR__);
var_dump(($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$a] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_49'
fn() => [(new PhptClassCloneThing())->id(), PhptClassCloneThing::inc(), PhptClassCloneThing::inc()]
MODULE_ENTRY_49
, get_defined_vars(), __DIR__));
var_dump(($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$b] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_50'
fn() => [(new PhptClassCloneThing())->id(), PhptClassCloneThing::inc()]
MODULE_ENTRY_50
, get_defined_vars(), __DIR__));

$inheritA = 'phpt_class_clone_inherit_a';
$inheritB = 'phpt_class_clone_inherit_b';
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$inheritA] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_51'
fn() => eval('const PHPT_CLASS_CLONE_SUFFIX = "A"; class PhptClassCloneBase { public function id(): string { return "base-"; } }')
MODULE_ENTRY_51
, get_defined_vars(), __DIR__);
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$inheritB] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_52'
fn() => eval('const PHPT_CLASS_CLONE_SUFFIX = "B"; class PhptClassCloneBase { public function id(): string { return "base-"; } }')
MODULE_ENTRY_52
, get_defined_vars(), __DIR__);

($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$inheritA] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_53'
function (): void {
    class PhptClassCloneChild extends PhptClassCloneBase {
        public function id(): string {
            return parent::id() . PHPT_CLASS_CLONE_SUFFIX;
        }
    }
}
MODULE_ENTRY_53
, get_defined_vars(), __DIR__);
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$inheritB] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_54'
function (): void {
    class PhptClassCloneChild extends PhptClassCloneBase {
        public function id(): string {
            return parent::id() . PHPT_CLASS_CLONE_SUFFIX;
        }
    }
}
MODULE_ENTRY_54
, get_defined_vars(), __DIR__);
var_dump(($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$inheritA] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_55'
fn() => (new PhptClassCloneChild())->id()
MODULE_ENTRY_55
, get_defined_vars(), __DIR__));
var_dump(($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$inheritB] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_56'
fn() => (new PhptClassCloneChild())->id()
MODULE_ENTRY_56
, get_defined_vars(), __DIR__));

$templateOwner = 'phpt_class_clone_template_owner';
$target = 'phpt_class_clone_target';
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$templateOwner] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_57'
fn() => define('PHPT_CLASS_CLONE_OWNED_VALUE', 'target')
MODULE_ENTRY_57
, get_defined_vars(), __DIR__);
$ownedTemplate = ($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$templateOwner] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_58'
fn() => eval('return function (): void { class PhptClassCloneOwnedTemplate { public static function id(): string { return PHPT_CLASS_CLONE_OWNED_VALUE; } } };')
MODULE_ENTRY_58
, get_defined_vars(), __DIR__);
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$target] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_59'
$ownedTemplate
MODULE_ENTRY_59
, get_defined_vars(), __DIR__);
var_dump(($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$templateOwner] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_60'
fn() => PhptClassCloneOwnedTemplate::id()
MODULE_ENTRY_60
, get_defined_vars(), __DIR__));

$anonA = 'phpt_class_clone_anon_a';
$anonB = 'phpt_class_clone_anon_b';
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$anonA] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_61'
fn() => define('PHPT_CLASS_CLONE_ANON_VALUE', 'anon-A')
MODULE_ENTRY_61
, get_defined_vars(), __DIR__);
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$anonB] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_62'
fn() => define('PHPT_CLASS_CLONE_ANON_VALUE', 'anon-B')
MODULE_ENTRY_62
, get_defined_vars(), __DIR__);
var_dump(($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$anonA] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_63'
fn() => new class {
    public function id(): string {
        return PHPT_CLASS_CLONE_ANON_VALUE;
    }
}
MODULE_ENTRY_63
, get_defined_vars(), __DIR__)->id());
var_dump(($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$anonB] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_64'
fn() => new class {
    public function id(): string {
        return PHPT_CLASS_CLONE_ANON_VALUE;
    }
}
MODULE_ENTRY_64
, get_defined_vars(), __DIR__)->id());

$anonDependency = 'phpt_class_clone_anon_dependency';
$anonDependant = 'phpt_class_clone_anon_dependant';
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$anonDependant] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_65'
fn() => \RuntimeModule::current()->addDependency($GLOBALS["runtime_modules"][$anonDependency] ??= new \RuntimeModule())
MODULE_ENTRY_65
, get_defined_vars(), __DIR__);
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$anonDependency] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_66'
fn() => new class {}
MODULE_ENTRY_66
, get_defined_vars(), __DIR__);
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$anonDependant] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_67'
fn() => new class {}
MODULE_ENTRY_67
, get_defined_vars(), __DIR__);
echo "anonymous dependency clones\n";

$lateAnonDependency = 'phpt_class_clone_late_anon_dependency';
$lateAnonDependant = 'phpt_class_clone_late_anon_dependant';
$dependencyAnon = ($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$lateAnonDependency] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_68'
fn() => new class {}
MODULE_ENTRY_68
, get_defined_vars(), __DIR__);
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$lateAnonDependant] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_69'
fn() => \RuntimeModule::current()->addDependency($GLOBALS["runtime_modules"][$lateAnonDependency] ??= new \RuntimeModule())
MODULE_ENTRY_69
, get_defined_vars(), __DIR__);
$dependantAnon = ($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$lateAnonDependant] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_70'
fn() => new class {}
MODULE_ENTRY_70
, get_defined_vars(), __DIR__);
var_dump(!(new ReflectionClass($dependencyAnon))->isInstance($dependantAnon));

$anonOwner = 'phpt_class_clone_anon_owner';
$anonTarget = 'phpt_class_clone_anon_target';
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$anonOwner] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_71'
fn() => define('PHPT_CLASS_CLONE_OWNED_ANON_VALUE', 'owned-anon')
MODULE_ENTRY_71
, get_defined_vars(), __DIR__);
$ownedAnonFactory = ($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$anonOwner] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_72'
fn() => eval('return fn() => new class { public function id(): string { return PHPT_CLASS_CLONE_OWNED_ANON_VALUE; } };')
MODULE_ENTRY_72
, get_defined_vars(), __DIR__);
var_dump(($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$anonTarget] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_73'
$ownedAnonFactory
MODULE_ENTRY_73
, get_defined_vars(), __DIR__)->id());
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
