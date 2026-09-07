--TEST--
Runtime modules: shutdown callbacks retain callable identity and execution context
--FILE--
<?php
$a = 'phpt_shutdown_a';
$b = 'phpt_shutdown_b';
$valueA = 'phpt_shutdown_value_a';
$registration = 'phpt_shutdown_registration';
$internalRegistration = 'phpt_shutdown_internal_registration';

function phpt_shutdown_nested_callback(): void {
    echo "nested:root\n";
}

module_run($a, fn() => module_add_dependency($valueA));
module_run($registration, fn() => module_add_dependency($a));

module_run($valueA, static fn() => eval(
    'const PHPT_SHUTDOWN_VALUE = "A";'
));
module_run($a, static fn() => eval(<<<'PHP'
    function phpt_shutdown_callback(): void {
        echo "callback:", constant("PHPT_SHUTDOWN_VALUE"), "\n";
    }
PHP));
module_run($b, static fn() => eval(<<<'PHP'
    const PHPT_SHUTDOWN_VALUE = "B";
    function phpt_shutdown_callback(): void {
        echo "callback:", constant("PHPT_SHUTDOWN_VALUE"), "\n";
    }
PHP));
module_run($internalRegistration, static fn() => eval(<<<'PHP'
    function phpt_shutdown_context_check(): void {
        echo "context:", defined("PHPT_SHUTDOWN_LATE")
            ? constant("PHPT_SHUTDOWN_LATE") : "missing", "\n";
    }

    function phpt_shutdown_nested_callback(): void {
        echo "nested:module\n";
    }
PHP));

foreach ([$a, $b, $registration] as $module) {
    module_run($module, static fn() =>
        register_shutdown_function('phpt_shutdown_callback')
    );
}
module_run($internalRegistration, static function (): void {
    register_shutdown_function('define', 'PHPT_SHUTDOWN_LATE', 'registration');
    register_shutdown_function('phpt_shutdown_context_check');
    register_shutdown_function('call_user_func', 'phpt_shutdown_nested_callback');
});

echo "main\n";
?>
--EXPECT--
main
callback:A
callback:B
callback:A
context:registration
nested:module
