--TEST--
Runtime modules: root aliases of internal classes remain root-local symbols
--FILE--
<?php
class_alias(stdClass::class, 'strlen');

var_dump(class_exists('strlen', false));
var_dump(module_run('phpt_root_internal_alias', fn() => class_exists('strlen', false)));
var_dump(module_run('phpt_root_internal_alias', fn() => in_array(
    'strlen', get_declared_classes(), true
)));

module_run('phpt_root_internal_alias', fn() => eval('class strlen {}'));
var_dump(module_run('phpt_root_internal_alias', fn() => new strlen()));
?>
--EXPECTF--
bool(true)
bool(false)
bool(false)
object(strlen)#%d (0) {
}
