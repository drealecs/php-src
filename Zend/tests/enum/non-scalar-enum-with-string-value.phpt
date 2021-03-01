--TEST--
Non-scalar enum errors when case has string value
--FILE--
<?php

enum Foo {
    case Bar = 'Bar';
}

?>
--EXPECTF--
Fatal error: Case Bar of non-scalar enum Foo must not have a value, try adding ": string" to the enum declaration in %s on line %d
