--TEST--
Runtime modules: same-spelled trait property defaults resolve through each trait owner
--FILE--
<?php
require_once __DIR__ . '/runtime_modules/file_fixture.inc';

$dependencyOne = 'phpt_trait_default_dependency_one';
$dependencyTwo = 'phpt_trait_default_dependency_two';
$traitOne = 'phpt_trait_default_owner_one';
$traitTwo = 'phpt_trait_default_owner_two';
$consumer = 'phpt_trait_default_consumer';

($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$traitOne] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_334'
fn() => \RuntimeModule::current()->addDependency($GLOBALS["runtime_modules"][$dependencyOne] ??= new \RuntimeModule())
MODULE_ENTRY_334
, get_defined_vars(), __DIR__);
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$traitTwo] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_335'
fn() => \RuntimeModule::current()->addDependency($GLOBALS["runtime_modules"][$dependencyTwo] ??= new \RuntimeModule())
MODULE_ENTRY_335
, get_defined_vars(), __DIR__);
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$consumer] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_336'
function () use ($traitOne, $traitTwo, $dependencyTwo): void {
    \RuntimeModule::current()->addDependency($GLOBALS["runtime_modules"][$traitOne] ??= new \RuntimeModule());
    \RuntimeModule::current()->addDependency($GLOBALS["runtime_modules"][$traitTwo] ??= new \RuntimeModule());
    \RuntimeModule::current()->addDependency($GLOBALS["runtime_modules"][$dependencyTwo] ??= new \RuntimeModule());
}
MODULE_ENTRY_336
, get_defined_vars(), __DIR__);

($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$dependencyOne] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_337'
static fn() => eval(<<<'PHP'
    class PhptSharedTraitDefault {
        public const VALUE = 'one';
    }
PHP)
MODULE_ENTRY_337
, get_defined_vars(), __DIR__);
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$dependencyTwo] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_338'
static fn() => eval(<<<'PHP'
    class PhptSharedTraitDefault {
        public const VALUE = 'two';
    }
PHP)
MODULE_ENTRY_338
, get_defined_vars(), __DIR__);

($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$traitOne] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_339'
static fn() => eval(<<<'PHP'
    trait PhptTraitDefaultOne {
        public string $item = PhptSharedTraitDefault::VALUE;
    }
PHP)
MODULE_ENTRY_339
, get_defined_vars(), __DIR__);
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$traitTwo] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_340'
static fn() => eval(<<<'PHP'
    trait PhptTraitDefaultTwo {
        public string $item = PhptSharedTraitDefault::VALUE;
    }
PHP)
MODULE_ENTRY_340
, get_defined_vars(), __DIR__);

($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$consumer] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_341'
static fn() => eval(<<<'PHP'
    class PhptTraitDefaultConsumer {
        use PhptTraitDefaultOne, PhptTraitDefaultTwo;
    }
PHP)
MODULE_ENTRY_341
, get_defined_vars(), __DIR__);

echo "unexpectedly composed\n";
?>
--EXPECTF--
Fatal error: PhptTraitDefaultOne and PhptTraitDefaultTwo define the same property ($item) in the composition of PhptTraitDefaultConsumer. However, the definition differs and is considered incompatible. Class was composed in %s on line %d
