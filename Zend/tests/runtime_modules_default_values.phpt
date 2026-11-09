--TEST--
Runtime modules: default values use their declaration owner
--FILE--
<?php
require_once __DIR__ . '/runtime_modules/file_fixture.inc';

($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['phpt_default_a'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_90'
static fn() => define('PHPT_RUNTIME_MODULE_DEFAULT', 'A')
MODULE_ENTRY_90
, get_defined_vars(), __DIR__);
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['phpt_default_b'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_91'
static fn() => define('PHPT_RUNTIME_MODULE_DEFAULT', 'B')
MODULE_ENTRY_91
, get_defined_vars(), __DIR__);

var_dump(($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['phpt_default_a'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_92'
function ($value = PHPT_RUNTIME_MODULE_DEFAULT): string {
    return $value;
}
MODULE_ENTRY_92
, get_defined_vars(), __DIR__));
var_dump(($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['phpt_default_b'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_93'
function ($value = PHPT_RUNTIME_MODULE_DEFAULT): string {
    return $value;
}
MODULE_ENTRY_93
, get_defined_vars(), __DIR__));
var_dump(($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['phpt_default_a'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_94'
function ($value = PHPT_RUNTIME_MODULE_DEFAULT): string {
    return $value;
}
MODULE_ENTRY_94
, get_defined_vars(), __DIR__));

foreach (['phpt_default_a' => 'A', 'phpt_default_b' => 'B'] as $module => $value) {
    ($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$module] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_95'
static fn() => eval(<<<PHP
        class PhptRuntimeModuleDefaultObject {
            public function value(): string { return '$value'; }
        }
        function phpt_runtime_module_default_function(): string { return '$value'; }
        class PhptRuntimeModuleDefaultStatic {
            public static function value(): string { return '$value'; }
        }
        class PhptRuntimeModuleDefaultClosure {
            public static Closure \$value = static function (): string {
                return PHPT_RUNTIME_MODULE_DEFAULT;
            };
        }
    PHP)
MODULE_ENTRY_95
, get_defined_vars(), __DIR__);
}

var_dump(($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['phpt_default_a'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_96'
function (
    $object = new PhptRuntimeModuleDefaultObject(),
    $function = phpt_runtime_module_default_function(...),
    $static = PhptRuntimeModuleDefaultStatic::value(...),
): array {
    return [$object->value(), $function(), $static()];
}
MODULE_ENTRY_96
, get_defined_vars(), __DIR__));
var_dump(($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['phpt_default_b'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_97'
function (
    $object = new PhptRuntimeModuleDefaultObject(),
    $function = phpt_runtime_module_default_function(...),
    $static = PhptRuntimeModuleDefaultStatic::value(...),
): array {
    return [$object->value(), $function(), $static()];
}
MODULE_ENTRY_97
, get_defined_vars(), __DIR__));
var_dump(($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['phpt_default_a'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_98'
function (
    $object = new PhptRuntimeModuleDefaultObject(),
    $function = phpt_runtime_module_default_function(...),
    $static = PhptRuntimeModuleDefaultStatic::value(...),
): array {
    return [$object->value(), $function(), $static()];
}
MODULE_ENTRY_98
, get_defined_vars(), __DIR__));

$closureA = ($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['phpt_default_a'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_99'
static fn() => PhptRuntimeModuleDefaultClosure::$value
MODULE_ENTRY_99
, get_defined_vars(), __DIR__);
$closureB = ($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['phpt_default_b'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_100'
static fn() => PhptRuntimeModuleDefaultClosure::$value
MODULE_ENTRY_100
, get_defined_vars(), __DIR__);
var_dump($closureA(), $closureB(), $closureA());

$reflection = ($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['phpt_default_a'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_101'
static function (): ReflectionFunction {
    eval(<<<'PHP'
        class PhptRuntimeModuleReflectedDefault {
            public function value(): string { return 'A'; }
        }
        function phpt_runtime_module_reflected_default(
            $value = new PhptRuntimeModuleReflectedDefault(),
            $reflection = new ReflectionClass('PhptRuntimeModuleReflectedDefault'),
        ): void {}
    PHP);
    return new ReflectionFunction('phpt_runtime_module_reflected_default');
}
MODULE_ENTRY_101
, get_defined_vars(), __DIR__);
var_dump($reflection->getParameters()[0]->getDefaultValue()->value());
var_dump($reflection->getParameters()[1]->getDefaultValue()->getName());
?>
--EXPECT--
string(1) "A"
string(1) "B"
string(1) "A"
array(3) {
  [0]=>
  string(1) "A"
  [1]=>
  string(1) "A"
  [2]=>
  string(1) "A"
}
array(3) {
  [0]=>
  string(1) "B"
  [1]=>
  string(1) "B"
  [2]=>
  string(1) "B"
}
array(3) {
  [0]=>
  string(1) "A"
  [1]=>
  string(1) "A"
  [2]=>
  string(1) "A"
}
string(1) "A"
string(1) "B"
string(1) "A"
string(1) "A"
string(33) "PhptRuntimeModuleReflectedDefault"
