--TEST--
Runtime modules: reflected internal partial defaults retain their evaluation context
--FILE--
<?php
function pfa_default_label(): string { return 'root'; }
function pfa_default($callback = call_user_func(?, ...)) {}

$rootParameter = new ReflectionParameter('pfa_default', 0);
$moduleParameter = module_run('pfa_defaults', function () {
    eval(<<<'PHP'
function pfa_default_label(): string { return 'module'; }
function pfa_default($callback = call_user_func(?, ...)) {}
PHP);
    return new ReflectionParameter('pfa_default', 0);
});

for ($i = 0; $i < 2; $i++) {
    $moduleDefault = $moduleParameter->getDefaultValue();
    echo $moduleDefault('pfa_default_label'), "\n";

    $rootDefault = module_run('pfa_defaults', fn() => $rootParameter->getDefaultValue());
    echo $rootDefault('pfa_default_label'), "\n";
}
?>
--EXPECT--
module
root
module
root
