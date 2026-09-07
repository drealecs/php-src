--TEST--
Runtime modules: explicit call context survives reentrant call setup
--FILE--
<?php

module_run('A', static function () {
    class PhptCallContextA {}
});

set_error_handler(static function () {
    echo "handler: ";
    var_dump(class_exists('PhptCallContextA', false));
    return true;
});

$callback = #[\Deprecated('trigger reentrant call setup')] static function () {
    echo "callback: ";
    var_dump(class_exists('PhptCallContextA', false));
};

module_run('A', $callback);

restore_error_handler();

?>
--EXPECT--
handler: bool(false)
callback: bool(true)
