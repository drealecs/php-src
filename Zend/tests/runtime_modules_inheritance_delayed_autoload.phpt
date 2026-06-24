--TEST--
Runtime modules: delayed variance autoloads are separated by owner module
--FILE--
<?php
require_once __DIR__ . '/runtime_modules/file_fixture.inc';

$calls = [];

($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['phpt_delayed_contract_one'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_181'
fn() => \RuntimeModule::current()->addDependency($GLOBALS["runtime_modules"]['phpt_delayed_autoload_one'] ??= new \RuntimeModule())
MODULE_ENTRY_181
, get_defined_vars(), __DIR__);
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['phpt_delayed_contract_two'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_182'
fn() => \RuntimeModule::current()->addDependency($GLOBALS["runtime_modules"]['phpt_delayed_autoload_two'] ??= new \RuntimeModule())
MODULE_ENTRY_182
, get_defined_vars(), __DIR__);
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['phpt_delayed_implementation'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_183'
static function (): void {
    \RuntimeModule::current()->addDependency($GLOBALS["runtime_modules"]['phpt_delayed_contract_one'] ??= new \RuntimeModule());
    \RuntimeModule::current()->addDependency($GLOBALS["runtime_modules"]['phpt_delayed_contract_two'] ??= new \RuntimeModule());
    \RuntimeModule::current()->addDependency($GLOBALS["runtime_modules"]['phpt_delayed_autoload_decoy'] ??= new \RuntimeModule());
}
MODULE_ENTRY_183
, get_defined_vars(), __DIR__);

$register = static function (string $module, string $label) use (&$calls): void {
    ($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$module] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_184'
static function () use (&$calls, $label): void {
        spl_autoload_register(
            static function (string $name) use (&$calls, $label): void {
                if ($name === 'PhptSharedDelayedType') {
                    $calls[] = $label;
                    eval('class PhptSharedDelayedType {}');
                }
            }
        );
    }
MODULE_ENTRY_184
, ['calls'=>&$calls] + get_defined_vars(), __DIR__);
};

$register('phpt_delayed_autoload_one', 'one');
$register('phpt_delayed_autoload_two', 'two');
$register('phpt_delayed_autoload_decoy', 'decoy');

($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['phpt_delayed_contract_one'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_185'
static fn() => eval(<<<'PHP'
    interface PhptDelayedContractOne {
        public function one(PhptSharedDelayedType $value): void;
    }
PHP)
MODULE_ENTRY_185
, get_defined_vars(), __DIR__);
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['phpt_delayed_contract_two'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_186'
static fn() => eval(<<<'PHP'
    interface PhptDelayedContractTwo {
        public function two(PhptSharedDelayedType $value): void;
    }
PHP)
MODULE_ENTRY_186
, get_defined_vars(), __DIR__);

($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['phpt_delayed_implementation'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_187'
static fn() => eval(<<<'PHP'
    class PhptDelayedImplementation implements PhptDelayedContractOne, PhptDelayedContractTwo {
        public function one(object $value): void {}
        public function two(object $value): void {}
    }
PHP)
MODULE_ENTRY_187
, get_defined_vars(), __DIR__);

sort($calls);
var_dump($calls);
echo "linked\n";
?>
--EXPECT--
array(2) {
  [0]=>
  string(3) "one"
  [1]=>
  string(3) "two"
}
linked
