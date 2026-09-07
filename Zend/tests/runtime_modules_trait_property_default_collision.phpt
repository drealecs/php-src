--TEST--
Runtime modules: same-spelled trait property defaults resolve through each trait owner
--FILE--
<?php
$dependencyOne = 'phpt_trait_default_dependency_one';
$dependencyTwo = 'phpt_trait_default_dependency_two';
$traitOne = 'phpt_trait_default_owner_one';
$traitTwo = 'phpt_trait_default_owner_two';
$consumer = 'phpt_trait_default_consumer';

module_run($traitOne, fn() => module_add_dependency($dependencyOne));
module_run($traitTwo, fn() => module_add_dependency($dependencyTwo));
module_run($consumer, function () use ($traitOne, $traitTwo, $dependencyTwo): void {
    module_add_dependency($traitOne);
    module_add_dependency($traitTwo);
    module_add_dependency($dependencyTwo);
});

module_run($dependencyOne, static fn() => eval(<<<'PHP'
    class PhptSharedTraitDefault {
        public const VALUE = 'one';
    }
PHP));
module_run($dependencyTwo, static fn() => eval(<<<'PHP'
    class PhptSharedTraitDefault {
        public const VALUE = 'two';
    }
PHP));

module_run($traitOne, static fn() => eval(<<<'PHP'
    trait PhptTraitDefaultOne {
        public string $item = PhptSharedTraitDefault::VALUE;
    }
PHP));
module_run($traitTwo, static fn() => eval(<<<'PHP'
    trait PhptTraitDefaultTwo {
        public string $item = PhptSharedTraitDefault::VALUE;
    }
PHP));

module_run($consumer, static fn() => eval(<<<'PHP'
    class PhptTraitDefaultConsumer {
        use PhptTraitDefaultOne, PhptTraitDefaultTwo;
    }
PHP));

echo "unexpectedly composed\n";
?>
--EXPECTF--
Fatal error: PhptTraitDefaultOne and PhptTraitDefaultTwo define the same property ($item) in the composition of PhptTraitDefaultConsumer. However, the definition differs and is considered incompatible. Class was composed in %s on line %d
