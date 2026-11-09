--TEST--
Runtime modules: shutdown callbacks retain callable identity and execution context
--FILE--
<?php
require_once __DIR__ . '/../../../../Zend/tests/runtime_modules/file_fixture.inc';

$a = 'phpt_shutdown_a';
$b = 'phpt_shutdown_b';
$valueA = 'phpt_shutdown_value_a';
$registration = 'phpt_shutdown_registration';
$internalRegistration = 'phpt_shutdown_internal_registration';

function phpt_shutdown_nested_callback(): void {
    echo "nested:root\n";
}

($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$a] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_430'
fn() => \RuntimeModule::current()->addDependency($GLOBALS["runtime_modules"][$valueA] ??= new \RuntimeModule())
MODULE_ENTRY_430
, get_defined_vars(), __DIR__);
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$registration] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_431'
fn() => \RuntimeModule::current()->addDependency($GLOBALS["runtime_modules"][$a] ??= new \RuntimeModule())
MODULE_ENTRY_431
, get_defined_vars(), __DIR__);

($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$valueA] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_432'
static fn() => eval(
    'const PHPT_SHUTDOWN_VALUE = "A";'
)
MODULE_ENTRY_432
, get_defined_vars(), __DIR__);
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$a] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_433'
static fn() => eval(<<<'PHP'
    function phpt_shutdown_callback(): void {
        echo "callback:", constant("PHPT_SHUTDOWN_VALUE"), "\n";
    }
PHP)
MODULE_ENTRY_433
, get_defined_vars(), __DIR__);
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$b] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_434'
static fn() => eval(<<<'PHP'
    const PHPT_SHUTDOWN_VALUE = "B";
    function phpt_shutdown_callback(): void {
        echo "callback:", constant("PHPT_SHUTDOWN_VALUE"), "\n";
    }
PHP)
MODULE_ENTRY_434
, get_defined_vars(), __DIR__);
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$internalRegistration] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_435'
static fn() => eval(<<<'PHP'
    function phpt_shutdown_context_check(): void {
        echo "context:", defined("PHPT_SHUTDOWN_LATE")
            ? constant("PHPT_SHUTDOWN_LATE") : "missing", "\n";
    }

    function phpt_shutdown_nested_callback(): void {
        echo "nested:module\n";
    }
PHP)
MODULE_ENTRY_435
, get_defined_vars(), __DIR__);

foreach ([$a, $b, $registration] as $module) {
    ($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$module] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_436'
static fn() =>
        register_shutdown_function('phpt_shutdown_callback')
MODULE_ENTRY_436
, get_defined_vars(), __DIR__);
}
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$internalRegistration] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_437'
static function (): void {
    register_shutdown_function('define', 'PHPT_SHUTDOWN_LATE', 'registration');
    register_shutdown_function('phpt_shutdown_context_check');
    register_shutdown_function('call_user_func', 'phpt_shutdown_nested_callback');
}
MODULE_ENTRY_437
, get_defined_vars(), __DIR__);

echo "main\n";
?>
--EXPECT--
main
callback:A
callback:B
callback:A
context:registration
nested:module
