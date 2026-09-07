--TEST--
Runtime modules: inheritance variance resolves through each declaring owner
--FILE--
<?php
$a = 'phpt_inheritance_owner_a';
$b = 'phpt_inheritance_owner_b';
$c = 'phpt_inheritance_owner_c';

module_run($a, fn() => module_add_dependency($b));
module_run($b, fn() => module_add_dependency($c));

module_run($c, fn() => eval('class PhptInheritanceOwnerHidden {}'));
module_run($b, fn() => eval(<<<'PHP'
    interface PhptInheritanceOwnerContract {
        public function accept(PhptInheritanceOwnerHidden $value): void;
    }
PHP));

module_run($a, fn() => eval(<<<'PHP'
    class PhptInheritanceOwnerImplementation implements PhptInheritanceOwnerContract {
        public function accept(object $value): void {}
    }
PHP));

var_dump(module_run($a, fn() => new PhptInheritanceOwnerImplementation()));
?>
--EXPECTF--
object(PhptInheritanceOwnerImplementation)#%d (0) {
}
