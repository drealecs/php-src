--TEST--
Runtime modules: internal header callbacks retain an explicit root registration
--FILE--
<?php
header_register_callback('spl_autoload_register');
module_run('header-trigger', static function (): void {
    echo "headers\n";
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
