--TEST--
Non-scalar enum errors when case has int value
--FILE--
<?php

enum Foo {
    case Bar = 1;
}

?>
--EXPECTF--
Fatal error: Case Bar of non-scalar enum Foo must not have a value, try adding ": int" to the enum declaration in %s on line %d
