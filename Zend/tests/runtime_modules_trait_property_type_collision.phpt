--TEST--
Runtime modules: same-spelled trait property types from different owners are incompatible
--FILE--
<?php
require_once __DIR__ . '/runtime_modules/file_fixture.inc';

$dependencyOne = 'phpt_trait_type_dependency_one';
$dependencyTwo = 'phpt_trait_type_dependency_two';
$traitOne = 'phpt_trait_type_owner_one';
$traitTwo = 'phpt_trait_type_owner_two';
$consumer = 'phpt_trait_type_consumer';

($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$traitOne] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_350'
fn() => \RuntimeModule::current()->addDependency($GLOBALS["runtime_modules"][$dependencyOne] ??= new \RuntimeModule())
MODULE_ENTRY_350
, get_defined_vars(), __DIR__);
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$traitTwo] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_351'
fn() => \RuntimeModule::current()->addDependency($GLOBALS["runtime_modules"][$dependencyTwo] ??= new \RuntimeModule())
MODULE_ENTRY_351
, get_defined_vars(), __DIR__);
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$consumer] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_352'
function () use ($traitOne, $traitTwo, $dependencyTwo): void {
    \RuntimeModule::current()->addDependency($GLOBALS["runtime_modules"][$traitOne] ??= new \RuntimeModule());
    \RuntimeModule::current()->addDependency($GLOBALS["runtime_modules"][$traitTwo] ??= new \RuntimeModule());
    \RuntimeModule::current()->addDependency($GLOBALS["runtime_modules"][$dependencyTwo] ??= new \RuntimeModule());
}
MODULE_ENTRY_352
, get_defined_vars(), __DIR__);

($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$dependencyOne] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_353'
static fn() => eval(
    'enum PhptSharedTraitType { case Value; }'
)
MODULE_ENTRY_353
, get_defined_vars(), __DIR__);
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$dependencyTwo] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_354'
static fn() => eval(
    'enum PhptSharedTraitType { case Value; }'
)
MODULE_ENTRY_354
, get_defined_vars(), __DIR__);

($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$traitOne] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_355'
static fn() => eval(<<<'PHP'
    trait PhptTraitTypeOne {
        public PhptSharedTraitType $item = PhptSharedTraitType::Value;
    }
PHP)
MODULE_ENTRY_355
, get_defined_vars(), __DIR__);
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$traitTwo] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_356'
static fn() => eval(<<<'PHP'
    trait PhptTraitTypeTwo {
        public PhptSharedTraitType $item = PhptSharedTraitType::Value;
    }
PHP)
MODULE_ENTRY_356
, get_defined_vars(), __DIR__);

($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$consumer] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_357'
static fn() => eval(<<<'PHP'
    class PhptTraitTypeConsumer {
        use PhptTraitTypeOne, PhptTraitTypeTwo;
    }
PHP)
MODULE_ENTRY_357
, get_defined_vars(), __DIR__);

echo "unexpectedly composed\n";
?>
--EXPECTF--
Fatal error: PhptTraitTypeOne and PhptTraitTypeTwo define the same property ($item) in the composition of PhptTraitTypeConsumer. However, the definition differs and is considered incompatible. Class was composed in %s on line %d
