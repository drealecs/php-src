--TEST--
Runtime modules: duplicate untyped trait properties are compatible across module owners
--FILE--
<?php
module_run('property-consumer', static fn() => module_add_dependency('property-trait'));
module_run('property-trait', static fn() => eval(<<<'PHP'
    trait UntypedProperties {
        public $value = 1;
        public static $staticValue = 2;
    }
PHP));
$object = module_run('property-consumer', static function (): object {
    class UntypedConsumer {
        use UntypedProperties;
        public $value = 1;
        public static $staticValue = 2;
    }
    return new UntypedConsumer();
});
var_dump($object->value, $object::$staticValue);
?>
--EXPECT--
int(1)
int(2)
