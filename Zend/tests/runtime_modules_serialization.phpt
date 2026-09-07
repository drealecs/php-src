--TEST--
Runtime modules: serialize keeps bare names and unserialize resolves by module
--FILE--
<?php
$m1 = 'phpt_serialize_m1';
$m2 = 'phpt_serialize_m2';
module_run($m1, fn() => eval('class PhptSerialThing { function id() { return 1; } }'));
module_run($m2, fn() => eval('class PhptSerialThing { function id() { return 2; } }'));
$serialized = module_run($m1, fn() => serialize(new PhptSerialThing()));
var_dump(str_contains($serialized, '"PhptSerialThing"'));
var_dump(module_run($m1, fn() => unserialize($serialized)->id()));
var_dump(module_run($m2, fn() => unserialize($serialized)->id()));
?>
--EXPECT--
bool(true)
int(1)
int(2)
