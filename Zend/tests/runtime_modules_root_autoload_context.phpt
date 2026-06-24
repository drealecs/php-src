--TEST--
Runtime modules: root-owned lazy symbols autoload in the root context
--FILE--
<?php
require_once __DIR__ . '/runtime_modules/file_fixture.inc';


spl_autoload_register(static function (string $class) {
    echo "autoload: $class\n";
    eval('#[Attribute] class PhptRootLazyAttribute {}');
});

#[PhptRootLazyAttribute]
class PhptRootAttributedClass {}

$attribute = (new ReflectionClass(PhptRootAttributedClass::class))->getAttributes()[0];

($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['A'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_290'
static function () use ($attribute) {
    var_dump($attribute->newInstance()::class);
}
MODULE_ENTRY_290
, get_defined_vars(), __DIR__);

?>
--EXPECT--
autoload: PhptRootLazyAttribute
string(21) "PhptRootLazyAttribute"
