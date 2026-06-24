--TEST--
Runtime modules: classes are destroyed in dependency order across modules
--FILE--
<?php
require_once __DIR__ . '/runtime_modules/file_fixture.inc';

$a = 'phpt_teardown_a';
$b = 'phpt_teardown_b';

($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$a] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_74'
fn() => \RuntimeModule::current()->addDependency($GLOBALS["runtime_modules"][$b] ??= new \RuntimeModule())
MODULE_ENTRY_74
, get_defined_vars(), __DIR__);
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$b] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_75'
fn() => \RuntimeModule::current()->addDependency($GLOBALS["runtime_modules"][$a] ??= new \RuntimeModule())
MODULE_ENTRY_75
, get_defined_vars(), __DIR__);

($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$a] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_76'
fn() => eval(<<<'PHP'
    interface PhptTeardownContract {}
    class PhptTeardownBase implements PhptTeardownContract {
        public const VALUE = ['base'];
        public int $property = 1;
    }
PHP)
MODULE_ENTRY_76
, get_defined_vars(), __DIR__);

($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$b] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_77'
fn() => eval(<<<'PHP'
    class PhptTeardownMid extends PhptTeardownBase {
        public static array $state = ['mid'];
    }
    class_alias(PhptTeardownBase::class, 'PhptTeardownAlias');
PHP)
MODULE_ENTRY_77
, get_defined_vars(), __DIR__);

($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$a] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_78'
fn() => eval(<<<'PHP'
    class PhptTeardownLeaf extends PhptTeardownMid {}
PHP)
MODULE_ENTRY_78
, get_defined_vars(), __DIR__);

\RuntimeModule::current()->addDependency($GLOBALS["runtime_modules"][$a] ??= new \RuntimeModule());
class PhptTeardownRootLeaf extends PhptTeardownLeaf {}

$object = new PhptTeardownRootLeaf();
var_dump($object instanceof PhptTeardownContract);
var_dump($object->property, PhptTeardownRootLeaf::VALUE, PhptTeardownRootLeaf::$state);
?>
--EXPECT--
bool(true)
int(1)
array(1) {
  [0]=>
  string(4) "base"
}
array(1) {
  [0]=>
  string(3) "mid"
}
