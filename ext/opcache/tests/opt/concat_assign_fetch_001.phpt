--TEST--
Optimize fetch/concat/assign into FETCH_RW + ASSIGN_OP
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

function vv($n) {
    $$n = $$n . "a";
}

function glob_case() {
    $GLOBALS['x'] = $GLOBALS['x'] . "a";
}

function no_change($n) {
    $$n = "a" . $$n;
}

?>
--EXPECTF--
$_main:
     ; (lines=1, args=0, vars=0, tmps=0)
     ; (after optimizer)
     ; %sconcat_assign_fetch_001.php:1-16
0000 RETURN int(1)

vv:
     ; (lines=4, args=1, vars=1, tmps=1)
     ; (after optimizer)
     ; %sconcat_assign_fetch_001.php:3-5
0000 CV0($n) = RECV 1
0001 V1 = FETCH_RW (local) CV0($n)
0002 ASSIGN_OP (CONCAT) V1 string("a")
0003 RETURN null

glob_case:
     ; (lines=3, args=0, vars=0, tmps=1)
     ; (after optimizer)
     ; %sconcat_assign_fetch_001.php:7-9
0000 V0 = FETCH_RW (global) string("x")
0001 ASSIGN_OP (CONCAT) V0 string("a")
0002 RETURN null

no_change:
     ; (lines=6, args=1, vars=1, tmps=2)
     ; (after optimizer)
     ; %sconcat_assign_fetch_001.php:11-13
0000 CV0($n) = RECV 1
0001 T1 = FETCH_R (local) CV0($n)
0002 T2 = CONCAT string("a") T1
0003 V1 = FETCH_W (local) CV0($n)
0004 ASSIGN V1 T2
0005 RETURN null
LIVE RANGES:
     2: 0003 - 0004 (tmp/var)
