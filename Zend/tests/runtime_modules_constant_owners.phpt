--TEST--
Runtime modules: constant expressions resolve through the declaring owner
--FILE--
<?php
require_once __DIR__ . '/runtime_modules/file_fixture.inc';

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
    ($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$module] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_79'
fn() => eval(str_replace('MODULE_VALUE', var_export($module, true), $code))
MODULE_ENTRY_79
, get_defined_vars(), __DIR__);
}

var_dump($foldProbe());
var_dump(($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['A'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_80'
fn() => PhptConstantOwnerFold::VALUE
MODULE_ENTRY_80
, get_defined_vars(), __DIR__));
var_dump(($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['B'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_81'
fn() => PhptConstantOwnerFold::VALUE
MODULE_ENTRY_81
, get_defined_vars(), __DIR__));
var_dump($constantFoldProbe());
var_dump(($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['A'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_82'
fn() => PHPT_CONSTANT_OWNER_FOLD
MODULE_ENTRY_82
, get_defined_vars(), __DIR__));
var_dump(($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['B'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_83'
fn() => PHPT_CONSTANT_OWNER_FOLD
MODULE_ENTRY_83
, get_defined_vars(), __DIR__));

foreach (['A', 'B'] as $module) {
    [$class, $function, $attribute] = ($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$module] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_84'
fn() => [
        new ReflectionClass('PhptConstantOwnerHolder'),
        new ReflectionFunction('phpt_constant_owner_function'),
        (new ReflectionFunction('phpt_constant_owner_function'))->getAttributes()[0],
    ]
MODULE_ENTRY_84
, get_defined_vars(), __DIR__);

    var_dump($class->getConstant('VALUE'));
    var_dump($class->getDefaultProperties()['property']);
    var_dump($function->getParameters()[0]->getDefaultValue());
    var_dump($attribute->getArguments()[0]);
    var_dump($attribute->newInstance()->value);
    var_dump(($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$module] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_85'
fn() => constant('PHPT_CONSTANT_OWNER_VALUE')
MODULE_ENTRY_85
, get_defined_vars(), __DIR__));
}

($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['attribute-dependency'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_86'
fn() => define('PHPT_DEPRECATED_MESSAGE', 'owner-message')
MODULE_ENTRY_86
, get_defined_vars(), __DIR__);
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['attribute-owner'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_87'
function (): void {
    \RuntimeModule::current()->addDependency($GLOBALS["runtime_modules"]['attribute-dependency'] ??= new \RuntimeModule());
    eval('#[Deprecated(PHPT_DEPRECATED_MESSAGE)] function phpt_constant_owner_deprecated() {}');
}
MODULE_ENTRY_87
, get_defined_vars(), __DIR__);
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['attribute-caller'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_88'
fn() => \RuntimeModule::current()->addDependency($GLOBALS["runtime_modules"]['attribute-owner'] ??= new \RuntimeModule())
MODULE_ENTRY_88
, get_defined_vars(), __DIR__);
set_error_handler(static function (int $severity, string $message): bool {
    echo $message, "\n";
    return true;
});
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['attribute-caller'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_89'
fn() => phpt_constant_owner_deprecated()
MODULE_ENTRY_89
, get_defined_vars(), __DIR__);
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
