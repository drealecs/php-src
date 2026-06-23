--TEST--
JIT: FETCH_R/CONCAT/FETCH_W/ASSIGN optimization compatibility
--INI--
opcache.enable=1
opcache.enable_cli=1
opcache.jit_buffer_size=16M
opcache.jit=tracing
--EXTENSIONS--
opcache
--FILE--
<?php

function vv_case(string $name): string {
    $$name = 'x';
    $$name = $$name . 'a';
    return $$name;
}

function global_case(): string {
    $GLOBALS['x'] = 'x';
    $GLOBALS['x'] = $GLOBALS['x'] . 'a';
    return $GLOBALS['x'];
}

$a = '';
$b = '';
for ($i = 0; $i < 20000; $i++) {
    $a = vv_case('v');
    $b = global_case();
}

var_dump($a, $b);
?>
--EXPECT--
string(2) "xa"
string(2) "xa"
