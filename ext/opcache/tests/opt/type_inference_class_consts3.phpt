--TEST--
Test type inference of class consts - reference by class name
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

class Test1 {
    public const FOO = 42;
    public final const BAR = 42;
    public const int BAZ = 42;
}

class Test3 {
    public function getTestFoo(): int {
        return Test1::FOO;
    }

    public function getTestBar(): int {
        return Test1::BAR;
    }

    public function getTestBaz(): int {
        return Test1::BAZ;
    }
}

?>
--EXPECTF--
$_main:
     ; (lines=1, args=0, vars=0, tmps=0)
     ; (after optimizer)
     ; %s
0000 RETURN int(1)

Test3::getTestFoo:
     ; (lines=3, args=0, vars=0, tmps=1)
     ; (after optimizer)
     ; %s
0000 T0 = FETCH_CLASS_CONSTANT string("Test1") string("FOO")
0001 VERIFY_RETURN_TYPE T0
0002 RETURN T0
LIVE RANGES:
     0: 0001 - 0002 (tmp/var)

Test3::getTestBar:
     ; (lines=3, args=0, vars=0, tmps=1)
     ; (after optimizer)
     ; %s
0000 T0 = FETCH_CLASS_CONSTANT string("Test1") string("BAR")
0001 VERIFY_RETURN_TYPE T0
0002 RETURN T0
LIVE RANGES:
     0: 0001 - 0002 (tmp/var)

Test3::getTestBaz:
     ; (lines=3, args=0, vars=0, tmps=1)
     ; (after optimizer)
     ; %s
0000 T0 = FETCH_CLASS_CONSTANT string("Test1") string("BAZ")
0001 VERIFY_RETURN_TYPE T0
0002 RETURN T0
LIVE RANGES:
     0: 0001 - 0002 (tmp/var)
