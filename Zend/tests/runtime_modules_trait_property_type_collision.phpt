--TEST--
Runtime modules: same-spelled trait property types from different owners are incompatible
--FILE--
<?php
$dependencyOne = 'phpt_trait_type_dependency_one';
$dependencyTwo = 'phpt_trait_type_dependency_two';
$traitOne = 'phpt_trait_type_owner_one';
$traitTwo = 'phpt_trait_type_owner_two';
$consumer = 'phpt_trait_type_consumer';

module_run($traitOne, fn() => module_add_dependency($dependencyOne));
module_run($traitTwo, fn() => module_add_dependency($dependencyTwo));
module_run($consumer, function () use ($traitOne, $traitTwo, $dependencyTwo): void {
    module_add_dependency($traitOne);
    module_add_dependency($traitTwo);
    module_add_dependency($dependencyTwo);
});

module_run($dependencyOne, static fn() => eval(
    'enum PhptSharedTraitType { case Value; }'
));
module_run($dependencyTwo, static fn() => eval(
    'enum PhptSharedTraitType { case Value; }'
));

module_run($traitOne, static fn() => eval(<<<'PHP'
    trait PhptTraitTypeOne {
        public PhptSharedTraitType $item = PhptSharedTraitType::Value;
    }
PHP));
module_run($traitTwo, static fn() => eval(<<<'PHP'
    trait PhptTraitTypeTwo {
        public PhptSharedTraitType $item = PhptSharedTraitType::Value;
    }
PHP));

module_run($consumer, static fn() => eval(<<<'PHP'
    class PhptTraitTypeConsumer {
        use PhptTraitTypeOne, PhptTraitTypeTwo;
    }
PHP));

echo "unexpectedly composed\n";
?>
--EXPECTF--
Fatal error: PhptTraitTypeOne and PhptTraitTypeTwo define the same property ($item) in the composition of PhptTraitTypeConsumer. However, the definition differs and is considered incompatible. Class was composed in %s on line %d
