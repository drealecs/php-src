--TEST--
Runtime modules: Reflection resolves declaration metadata through its owner module
--FILE--
<?php
/** Root trait. */
trait RuntimeModuleReflectionTrait {}

class RuntimeModuleReflectionType {}

module_run('reflection-dependency', static function (): void {
    eval('class RuntimeModuleReflectionType {}');
});
module_run('reflection-owner', static function (): void {
    module_add_dependency('reflection-dependency');
});

$object = module_run('reflection-owner', static function (): object {
    eval(<<<'PHP'
        /** Module trait. */
        trait RuntimeModuleReflectionTrait {}
        class RuntimeModuleReflectionConsumer {
            use RuntimeModuleReflectionTrait;
        }
        function runtime_module_reflection_parameter(RuntimeModuleReflectionType $value): void {}
        PHP);
    return new RuntimeModuleReflectionConsumer();
});
$dependencyValue = module_run('reflection-dependency', static function (): object {
    return new RuntimeModuleReflectionType();
});
$function = module_run('reflection-owner', static function (): Closure {
    return runtime_module_reflection_parameter(...);
});

$traits = (new ReflectionClass($object))->getTraits();
echo $traits['RuntimeModuleReflectionTrait']->getDocComment(), "\n";

$parameterClass = @(new ReflectionFunction($function))->getParameters()[0]->getClass();
var_dump($parameterClass->isInstance($dependencyValue));
?>
--EXPECT--
/** Module trait. */
bool(true)
