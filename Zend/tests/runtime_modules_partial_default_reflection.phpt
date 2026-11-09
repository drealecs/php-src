--TEST--
Runtime modules: reflected internal partial defaults retain their evaluation context
--FILE--
<?php
require_once __DIR__ . '/runtime_modules/file_fixture.inc';

function pfa_default_label(): string { return 'root'; }
function pfa_default($callback = call_user_func(?, ...)) {}

$rootParameter = new ReflectionParameter('pfa_default', 0);
$moduleParameter = ($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['pfa_defaults'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_245'
function () {
    eval(<<<'PHP'
function pfa_default_label(): string { return 'module'; }
function pfa_default($callback = call_user_func(?, ...)) {}
PHP);
    return new ReflectionParameter('pfa_default', 0);
}
MODULE_ENTRY_245
, get_defined_vars(), __DIR__);

for ($i = 0; $i < 2; $i++) {
    $moduleDefault = $moduleParameter->getDefaultValue();
    echo $moduleDefault('pfa_default_label'), "\n";

    $rootDefault = ($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['pfa_defaults'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_246'
fn() => $rootParameter->getDefaultValue()
MODULE_ENTRY_246
, get_defined_vars(), __DIR__);
    echo $rootDefault('pfa_default_label'), "\n";
}
?>
--EXPECT--
module
root
module
root
