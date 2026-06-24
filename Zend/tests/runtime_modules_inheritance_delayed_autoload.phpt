--TEST--
Runtime modules: delayed variance autoloads are separated by owner module
--FILE--
<?php
$calls = [];

module_run('phpt_delayed_contract_one', fn() => module_add_dependency('phpt_delayed_autoload_one'));
module_run('phpt_delayed_contract_two', fn() => module_add_dependency('phpt_delayed_autoload_two'));
module_run('phpt_delayed_implementation', static function (): void {
    module_add_dependency('phpt_delayed_contract_one');
    module_add_dependency('phpt_delayed_contract_two');
    module_add_dependency('phpt_delayed_autoload_decoy');
});

$register = static function (string $module, string $label) use (&$calls): void {
    module_run($module, static function () use (&$calls, $label): void {
        spl_autoload_register(
            static function (string $name) use (&$calls, $label): void {
                if ($name === 'PhptSharedDelayedType') {
                    $calls[] = $label;
                    eval('class PhptSharedDelayedType {}');
                }
            }
        );
    });
};

$register('phpt_delayed_autoload_one', 'one');
$register('phpt_delayed_autoload_two', 'two');
$register('phpt_delayed_autoload_decoy', 'decoy');

module_run('phpt_delayed_contract_one', static fn() => eval(<<<'PHP'
    interface PhptDelayedContractOne {
        public function one(PhptSharedDelayedType $value): void;
    }
PHP));
module_run('phpt_delayed_contract_two', static fn() => eval(<<<'PHP'
    interface PhptDelayedContractTwo {
        public function two(PhptSharedDelayedType $value): void;
    }
PHP));

module_run('phpt_delayed_implementation', static fn() => eval(<<<'PHP'
    class PhptDelayedImplementation implements PhptDelayedContractOne, PhptDelayedContractTwo {
        public function one(object $value): void {}
        public function two(object $value): void {}
    }
PHP));

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
