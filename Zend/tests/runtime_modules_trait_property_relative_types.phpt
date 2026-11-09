--TEST--
Runtime modules: compatible trait properties use consumer-relative self and parent types
--FILE--
<?php
require_once __DIR__ . '/runtime_modules/file_fixture.inc';

($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['relative_trait_one'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_342'
fn() => \RuntimeModule::current()->addDependency($GLOBALS["runtime_modules"]['relative_trait_types'] ??= new \RuntimeModule())
MODULE_ENTRY_342
, get_defined_vars(), __DIR__);
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['relative_trait_two'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_343'
fn() => \RuntimeModule::current()->addDependency($GLOBALS["runtime_modules"]['relative_trait_types'] ??= new \RuntimeModule())
MODULE_ENTRY_343
, get_defined_vars(), __DIR__);
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['relative_trait_consumer'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_344'
static function (): void {
    \RuntimeModule::current()->addDependency($GLOBALS["runtime_modules"]['relative_trait_one'] ??= new \RuntimeModule());
    \RuntimeModule::current()->addDependency($GLOBALS["runtime_modules"]['relative_trait_two'] ??= new \RuntimeModule());
}
MODULE_ENTRY_344
, get_defined_vars(), __DIR__);

($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['relative_trait_types'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_345'
static fn() => eval('class PhptRelativeTraitType {}')
MODULE_ENTRY_345
, get_defined_vars(), __DIR__);
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['relative_trait_one'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_346'
static fn() => eval(<<<'PHP'
    trait PhptRelativeTraitOne {
        public self $selfValue;
        public parent $parentValue;
        public PhptRelativeTraitType $typed;
    }
PHP)
MODULE_ENTRY_346
, get_defined_vars(), __DIR__);
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['relative_trait_two'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_347'
static fn() => eval(<<<'PHP'
    trait PhptRelativeTraitTwo {
        public self $selfValue;
        public parent $parentValue;
        public PhptRelativeTraitType $typed;
    }
PHP)
MODULE_ENTRY_347
, get_defined_vars(), __DIR__);

[$object, $parent] = ($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['relative_trait_consumer'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_348'
static function (): array {
    eval(<<<'PHP'
        class PhptRelativeTraitParent {}
        class PhptRelativeTraitConsumer extends PhptRelativeTraitParent {
            use PhptRelativeTraitOne, PhptRelativeTraitTwo;
        }
    PHP);
    return [new PhptRelativeTraitConsumer(), new PhptRelativeTraitParent()];
}
MODULE_ENTRY_348
, get_defined_vars(), __DIR__);
$typed = ($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['relative_trait_types'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_349'
static fn() => new PhptRelativeTraitType()
MODULE_ENTRY_349
, get_defined_vars(), __DIR__);
$object->selfValue = $object;
$object->parentValue = $parent;
$object->typed = $typed;
var_dump($object->selfValue === $object);
var_dump($object->parentValue === $parent);
var_dump($object->typed === $typed);
?>
--EXPECT--
bool(true)
bool(true)
bool(true)
