<?php
$owner = RuntimeModule::current();
$object = $GLOBALS['entry_root']->require(__DIR__ . '/root_entry.inc');
var_dump(RuntimeModule::current() === $owner);
return $object;
