--TEST--
Runtime modules: internal tick callbacks execute in their registration module
--FILE--
<?php
module_run('tick-owner', static function (): void {
    register_tick_function('spl_autoload_register');
});
module_run('tick-trigger', static fn() => eval('declare(ticks=1); $value = 1;'));
unregister_tick_function('spl_autoload_register');
module_run('tick-owner', static function (): void {
    var_dump(spl_autoload_functions());
    spl_autoload_unregister('spl_autoload');
});
module_run('tick-trigger', static fn() => var_dump(spl_autoload_functions()));
var_dump(spl_autoload_functions());

register_tick_function('spl_autoload_register');
module_run('tick-trigger', static fn() => eval('declare(ticks=1); $value = 1;'));
unregister_tick_function('spl_autoload_register');
module_run('tick-trigger', static fn() => var_dump(spl_autoload_functions()));
var_dump(spl_autoload_functions());
?>
--EXPECT--
array(1) {
  [0]=>
  string(12) "spl_autoload"
}
array(0) {
}
array(0) {
}
array(0) {
}
array(1) {
  [0]=>
  string(12) "spl_autoload"
}
