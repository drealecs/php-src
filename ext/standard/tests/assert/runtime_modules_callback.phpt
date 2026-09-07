--TEST--
Runtime modules: assert callbacks resolve in their registration module
--INI--
zend.assertions=1
assert.active=1
--FILE--
<?php
module_run('assert-a', static function (): void {
    eval('function runtime_module_assert_callback() { echo "A\\n"; }');
});
module_run('assert-b', static function (): void {
    eval('function runtime_module_assert_callback() { echo "B\\n"; }');
});

module_run('assert-a', static function (): void {
    @assert_options(ASSERT_CALLBACK, 'runtime_module_assert_callback');
});
module_run('assert-b', static function (): void {
    try {
        assert(false);
    } catch (AssertionError) {
    }
});

module_run('assert-a', static function (): void {
    @ini_set('assert.callback', 'runtime_module_assert_callback');
});
module_run('assert-b', static function (): void {
    try {
        assert(false);
    } catch (AssertionError) {
    }
});
?>
--EXPECT--
A
A
