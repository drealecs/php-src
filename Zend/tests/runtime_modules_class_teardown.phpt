--TEST--
Runtime modules: classes are destroyed in dependency order across modules
--FILE--
<?php
$a = 'phpt_teardown_a';
$b = 'phpt_teardown_b';

module_run($a, fn() => module_add_dependency($b));
module_run($b, fn() => module_add_dependency($a));

module_run($a, fn() => eval(<<<'PHP'
    interface PhptTeardownContract {}
    class PhptTeardownBase implements PhptTeardownContract {
        public const VALUE = ['base'];
        public int $property = 1;
    }
PHP));

module_run($b, fn() => eval(<<<'PHP'
    class PhptTeardownMid extends PhptTeardownBase {
        public static array $state = ['mid'];
    }
    class_alias(PhptTeardownBase::class, 'PhptTeardownAlias');
PHP));

module_run($a, fn() => eval(<<<'PHP'
    class PhptTeardownLeaf extends PhptTeardownMid {}
PHP));

module_add_dependency($a);
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
