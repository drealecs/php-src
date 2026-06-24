--TEST--
Runtime modules: internal header callbacks execute in their registration module
--FILE--
<?php
module_run('header-owner', static function (): void {
    header_register_callback('spl_autoload_register');
});
module_run('header-trigger', static function (): void {
    echo "headers\n";
    var_dump(spl_autoload_functions());
});
module_run('header-owner', static function (): void {
    var_dump(spl_autoload_functions());
});
var_dump(spl_autoload_functions());
?>
--EXPECT--
headers
array(0) {
}
array(1) {
  [0]=>
  string(12) "spl_autoload"
}
array(0) {
}
