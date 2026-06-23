--TEST--
Optimize CV concat assignment into ASSIGN_OP
--INI--
opcache.enable=1
opcache.enable_cli=1
opcache.optimization_level=-1
opcache.opt_debug_level=0x20000
opcache.preload=
zend_test.observer.enabled=0
--EXTENSIONS--
opcache
--FILE--
<?php

function f($s) {
    $s = $s . "y";
    return $s;
}

function g($s) {
    $s = "y" . $s;
    return $s;
}

?>
--EXPECTF--
$_main:
     ; (lines=1, args=0, vars=0, tmps=0)
     ; (after optimizer)
     ; %sconcat_assign_cv_001.php:1-14
0000 RETURN int(1)

f:
     ; (lines=3, args=1, vars=1, tmps=0)
     ; (after optimizer)
     ; %sconcat_assign_cv_001.php:3-6
0000 CV0($s) = RECV 1
0001 ASSIGN_OP (CONCAT) CV0($s) string("y")
0002 RETURN CV0($s)

g:
     ; (lines=4, args=1, vars=1, tmps=1)
     ; (after optimizer)
     ; %sconcat_assign_cv_001.php:8-11
0000 CV0($s) = RECV 1
0001 T1 = CONCAT string("y") CV0($s)
0002 ASSIGN CV0($s) T1
0003 RETURN CV0($s)
