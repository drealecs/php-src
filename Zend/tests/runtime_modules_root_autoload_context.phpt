--TEST--
Runtime modules: root-owned lazy symbols autoload in the root context
--FILE--
<?php

spl_autoload_register(static function (string $class) {
    echo "autoload: $class\n";
    eval('#[Attribute] class PhptRootLazyAttribute {}');
});

#[PhptRootLazyAttribute]
class PhptRootAttributedClass {}

$attribute = (new ReflectionClass(PhptRootAttributedClass::class))->getAttributes()[0];

module_run('A', static function () use ($attribute) {
    var_dump($attribute->newInstance()::class);
});

?>
--EXPECT--
autoload: PhptRootLazyAttribute
string(21) "PhptRootLazyAttribute"
