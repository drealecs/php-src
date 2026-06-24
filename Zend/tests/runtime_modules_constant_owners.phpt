--TEST--
Runtime modules: constant expressions resolve through the declaring owner
--FILE--
<?php
class PhptConstantOwnerFold {
    public const VALUE = 'root';
}
const PHPT_CONSTANT_OWNER_FOLD = 'root';

$foldProbe = fn() => PhptConstantOwnerFold::VALUE;
$constantFoldProbe = fn() => PHPT_CONSTANT_OWNER_FOLD;
$code = <<<'PHP'
const PHPT_CONSTANT_OWNER_VALUE = MODULE_VALUE;
const PHPT_CONSTANT_OWNER_FOLD = MODULE_VALUE;

class PhptConstantOwnerFold {
    public const VALUE = MODULE_VALUE;
}

class PhptConstantOwnerHolder {
    public const VALUE = PHPT_CONSTANT_OWNER_VALUE;
    public string $property = PHPT_CONSTANT_OWNER_VALUE;
}

#[Attribute]
class PhptConstantOwnerAttribute {
    public function __construct(public string $value) {}
}

#[PhptConstantOwnerAttribute(PHPT_CONSTANT_OWNER_VALUE)]
function phpt_constant_owner_function($value = PHPT_CONSTANT_OWNER_VALUE) {}
PHP;

foreach (['A', 'B'] as $module) {
    module_run($module, fn() => eval(str_replace('MODULE_VALUE', var_export($module, true), $code)));
}

var_dump($foldProbe());
var_dump(module_run('A', $foldProbe));
var_dump(module_run('B', $foldProbe));
var_dump($constantFoldProbe());
var_dump(module_run('A', $constantFoldProbe));
var_dump(module_run('B', $constantFoldProbe));

foreach (['A', 'B'] as $module) {
    [$class, $function, $attribute] = module_run($module, fn() => [
        new ReflectionClass('PhptConstantOwnerHolder'),
        new ReflectionFunction('phpt_constant_owner_function'),
        (new ReflectionFunction('phpt_constant_owner_function'))->getAttributes()[0],
    ]);

    var_dump($class->getConstant('VALUE'));
    var_dump($class->getDefaultProperties()['property']);
    var_dump($function->getParameters()[0]->getDefaultValue());
    var_dump($attribute->getArguments()[0]);
    var_dump($attribute->newInstance()->value);
    var_dump(module_run($module, fn() => constant('PHPT_CONSTANT_OWNER_VALUE')));
}

module_run('attribute-dependency', fn() => define('PHPT_DEPRECATED_MESSAGE', 'owner-message'));
module_run('attribute-owner', function (): void {
    module_add_dependency('attribute-dependency');
    eval('#[Deprecated(PHPT_DEPRECATED_MESSAGE)] function phpt_constant_owner_deprecated() {}');
});
module_run('attribute-caller', fn() => module_add_dependency('attribute-owner'));
set_error_handler(static function (int $severity, string $message): bool {
    echo $message, "\n";
    return true;
});
module_run('attribute-caller', fn() => phpt_constant_owner_deprecated());
restore_error_handler();
?>
--EXPECT--
string(4) "root"
string(1) "A"
string(1) "B"
string(4) "root"
string(1) "A"
string(1) "B"
string(1) "A"
string(1) "A"
string(1) "A"
string(1) "A"
string(1) "A"
string(1) "A"
string(1) "B"
string(1) "B"
string(1) "B"
string(1) "B"
string(1) "B"
string(1) "B"
Function phpt_constant_owner_deprecated() is deprecated, owner-message
