--TEST--
Runtime modules: default values use the explicit execution context
--FILE--
<?php
$probe = function ($value = PHPT_RUNTIME_MODULE_DEFAULT): string {
    return $value;
};

module_run('phpt_default_a', static fn() => define('PHPT_RUNTIME_MODULE_DEFAULT', 'A'));
module_run('phpt_default_b', static fn() => define('PHPT_RUNTIME_MODULE_DEFAULT', 'B'));

var_dump(module_run('phpt_default_a', $probe));
var_dump(module_run('phpt_default_b', $probe));
var_dump(module_run('phpt_default_a', $probe));

$callableProbe = function (
    $object = new PhptRuntimeModuleDefaultObject(),
    $function = phpt_runtime_module_default_function(...),
    $static = PhptRuntimeModuleDefaultStatic::value(...),
): array {
    return [$object->value(), $function(), $static()];
};

foreach (['phpt_default_a' => 'A', 'phpt_default_b' => 'B'] as $module => $value) {
    module_run($module, static fn() => eval(<<<PHP
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
    PHP));
}

var_dump(module_run('phpt_default_a', $callableProbe));
var_dump(module_run('phpt_default_b', $callableProbe));
var_dump(module_run('phpt_default_a', $callableProbe));

$closureA = module_run('phpt_default_a', static fn() => PhptRuntimeModuleDefaultClosure::$value);
$closureB = module_run('phpt_default_b', static fn() => PhptRuntimeModuleDefaultClosure::$value);
var_dump($closureA(), $closureB(), $closureA());

$reflection = module_run('phpt_default_a', static function (): ReflectionFunction {
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
});
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
