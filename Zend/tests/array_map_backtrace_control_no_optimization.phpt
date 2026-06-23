--TEST--
array_map() backtrace keeps internal frame for string callback
--FILE--
<?php

function process($value) {
    return debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
}

function test() {
    return array_map('process', [1])[0];
}

$trace = test();
foreach ($trace as $frame) {
    echo $frame['function'], "\n";
}

?>
--EXPECT--
process
array_map
test
