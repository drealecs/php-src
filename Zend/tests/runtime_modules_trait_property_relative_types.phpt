--TEST--
Runtime modules: compatible trait properties use consumer-relative self and parent types
--FILE--
<?php
module_run('relative_trait_one', fn() => module_add_dependency('relative_trait_types'));
module_run('relative_trait_two', fn() => module_add_dependency('relative_trait_types'));
module_run('relative_trait_consumer', static function (): void {
    module_add_dependency('relative_trait_one');
    module_add_dependency('relative_trait_two');
});

module_run('relative_trait_types', static fn() => eval('class PhptRelativeTraitType {}'));
module_run('relative_trait_one', static fn() => eval(<<<'PHP'
    trait PhptRelativeTraitOne {
        public self $selfValue;
        public parent $parentValue;
        public PhptRelativeTraitType $typed;
    }
PHP));
module_run('relative_trait_two', static fn() => eval(<<<'PHP'
    trait PhptRelativeTraitTwo {
        public self $selfValue;
        public parent $parentValue;
        public PhptRelativeTraitType $typed;
    }
PHP));

[$object, $parent] = module_run('relative_trait_consumer', static function (): array {
    eval(<<<'PHP'
        class PhptRelativeTraitParent {}
        class PhptRelativeTraitConsumer extends PhptRelativeTraitParent {
            use PhptRelativeTraitOne, PhptRelativeTraitTwo;
        }
    PHP);
    return [new PhptRelativeTraitConsumer(), new PhptRelativeTraitParent()];
});
$typed = module_run('relative_trait_types', static fn() => new PhptRelativeTraitType());
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
