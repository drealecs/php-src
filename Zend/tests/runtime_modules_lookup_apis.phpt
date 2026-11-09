--TEST--
Runtime modules: lookup APIs use current module visibility without autoload leakage
--FILE--
<?php
require_once __DIR__ . '/runtime_modules/file_fixture.inc';

$a = 'phpt_lookup_apis_a';
$b = 'phpt_lookup_apis_b';
$dep = 'phpt_lookup_apis_dep';
$main = 'phpt_lookup_apis_main';
$autoloadEvents = [];

($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$main] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_211'
fn() => \RuntimeModule::current()->addDependency($GLOBALS["runtime_modules"][$dep] ??= new \RuntimeModule())
MODULE_ENTRY_211
, get_defined_vars(), __DIR__);

($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$a] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_212'
function (): void {
    eval(<<<'PHP'
        class PhptLookupApisClass { public static function id(): string { return 'A'; } }
        interface PhptLookupApisInterface {}
        enum PhptLookupApisEnum { case A; }
    PHP);
}
MODULE_ENTRY_212
, get_defined_vars(), __DIR__);

($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$b] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_213'
function (): void {
    eval(<<<'PHP'
        class PhptLookupApisClass { public static function id(): string { return 'B'; } }
        interface PhptLookupApisInterface {}
        enum PhptLookupApisEnum { case B; }
    PHP);
}
MODULE_ENTRY_213
, get_defined_vars(), __DIR__);

($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$dep] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_214'
function (): void {
    eval(<<<'PHP'
        class PhptLookupApisDepClass {}
        interface PhptLookupApisDepInterface {}
        enum PhptLookupApisDepEnum { case Dep; }
    PHP);
}
MODULE_ENTRY_214
, get_defined_vars(), __DIR__);

($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$main] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_215'
function () use (&$autoloadEvents): void {
    spl_autoload_register(function (string $class) use (&$autoloadEvents): void {
        $autoloadEvents[] = $class;
    });
}
MODULE_ENTRY_215
, ['autoloadEvents'=>&$autoloadEvents] + get_defined_vars(), __DIR__);

var_dump(($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$a] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_216'
fn() => [
    class_exists('PhptLookupApisClass', false),
    interface_exists('PhptLookupApisInterface', false),
    enum_exists('PhptLookupApisEnum', false),
    PhptLookupApisClass::id(),
]
MODULE_ENTRY_216
, get_defined_vars(), __DIR__));

var_dump(($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$b] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_217'
fn() => [
    class_exists('PhptLookupApisClass', false),
    interface_exists('PhptLookupApisInterface', false),
    enum_exists('PhptLookupApisEnum', false),
    PhptLookupApisClass::id(),
]
MODULE_ENTRY_217
, get_defined_vars(), __DIR__));

var_dump(($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$main] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_218'
fn() => [
    class_exists('PhptLookupApisDepClass', false),
    interface_exists('PhptLookupApisDepInterface', false),
    enum_exists('PhptLookupApisDepEnum', false),
    class_exists('PhptLookupApisMissingClass', false),
]
MODULE_ENTRY_218
, get_defined_vars(), __DIR__));

var_dump($autoloadEvents);
?>
--EXPECT--
array(4) {
  [0]=>
  bool(true)
  [1]=>
  bool(true)
  [2]=>
  bool(true)
  [3]=>
  string(1) "A"
}
array(4) {
  [0]=>
  bool(true)
  [1]=>
  bool(true)
  [2]=>
  bool(true)
  [3]=>
  string(1) "B"
}
array(4) {
  [0]=>
  bool(true)
  [1]=>
  bool(true)
  [2]=>
  bool(true)
  [3]=>
  bool(false)
}
array(0) {
}
