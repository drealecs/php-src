--TEST--
Runtime modules: inheritance variance resolves through each declaring owner
--FILE--
<?php
require_once __DIR__ . '/runtime_modules/file_fixture.inc';

$a = 'phpt_inheritance_owner_a';
$b = 'phpt_inheritance_owner_b';
$c = 'phpt_inheritance_owner_c';

($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$a] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_188'
fn() => \RuntimeModule::current()->addDependency($GLOBALS["runtime_modules"][$b] ??= new \RuntimeModule())
MODULE_ENTRY_188
, get_defined_vars(), __DIR__);
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$b] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_189'
fn() => \RuntimeModule::current()->addDependency($GLOBALS["runtime_modules"][$c] ??= new \RuntimeModule())
MODULE_ENTRY_189
, get_defined_vars(), __DIR__);

($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$c] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_190'
fn() => eval('class PhptInheritanceOwnerHidden {}')
MODULE_ENTRY_190
, get_defined_vars(), __DIR__);
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$b] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_191'
fn() => eval(<<<'PHP'
    interface PhptInheritanceOwnerContract {
        public function accept(PhptInheritanceOwnerHidden $value): void;
    }
PHP)
MODULE_ENTRY_191
, get_defined_vars(), __DIR__);

($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$a] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_192'
fn() => eval(<<<'PHP'
    class PhptInheritanceOwnerImplementation implements PhptInheritanceOwnerContract {
        public function accept(object $value): void {}
    }
PHP)
MODULE_ENTRY_192
, get_defined_vars(), __DIR__);

var_dump(($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$a] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_193'
fn() => new PhptInheritanceOwnerImplementation()
MODULE_ENTRY_193
, get_defined_vars(), __DIR__));
?>
--EXPECTF--
object(PhptInheritanceOwnerImplementation)#%d (0) {
}
