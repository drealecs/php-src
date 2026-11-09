--TEST--
Runtime modules: Reflection resolves declaration metadata through its owner module
--FILE--
<?php
require_once __DIR__ . '/runtime_modules/file_fixture.inc';

/** Root trait. */
trait RuntimeModuleReflectionTrait {}

class RuntimeModuleReflectionType {}

($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['reflection-dependency'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_253'
static function (): void {
    eval('class RuntimeModuleReflectionType {}');
}
MODULE_ENTRY_253
, get_defined_vars(), __DIR__);
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['reflection-owner'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_254'
static function (): void {
    \RuntimeModule::current()->addDependency($GLOBALS["runtime_modules"]['reflection-dependency'] ??= new \RuntimeModule());
}
MODULE_ENTRY_254
, get_defined_vars(), __DIR__);

$object = ($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['reflection-owner'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_255'
static function (): object {
    eval(<<<'PHP'
        /** Module trait. */
        trait RuntimeModuleReflectionTrait {}
        class RuntimeModuleReflectionConsumer {
            use RuntimeModuleReflectionTrait;
        }
        function runtime_module_reflection_parameter(RuntimeModuleReflectionType $value): void {}
        PHP);
    return new RuntimeModuleReflectionConsumer();
}
MODULE_ENTRY_255
, get_defined_vars(), __DIR__);
$dependencyValue = ($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['reflection-dependency'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_256'
static function (): object {
    return new RuntimeModuleReflectionType();
}
MODULE_ENTRY_256
, get_defined_vars(), __DIR__);
$function = ($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['reflection-owner'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_257'
static function (): Closure {
    return runtime_module_reflection_parameter(...);
}
MODULE_ENTRY_257
, get_defined_vars(), __DIR__);

$traits = (new ReflectionClass($object))->getTraits();
echo $traits['RuntimeModuleReflectionTrait']->getDocComment(), "\n";

$parameterClass = @(new ReflectionFunction($function))->getParameters()[0]->getClass();
var_dump($parameterClass->isInstance($dependencyValue));
?>
--EXPECT--
/** Module trait. */
bool(true)
