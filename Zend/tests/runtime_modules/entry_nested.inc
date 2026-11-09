<?php
$local = 'outer';
$owner = RuntimeModule::current();
$result = $GLOBALS['nested_module']->require(__DIR__ . '/entry_context.inc');
var_dump($local, RuntimeModule::current() === $owner);
return $result;
