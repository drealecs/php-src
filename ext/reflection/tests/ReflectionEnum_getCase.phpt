--TEST--
ReflectionEnum::getCases()
--FILE--
<?php

enum Enum_ {
    case Foo;
    const Bar = self::Foo;
}

enum IntEnum: int {
    case Foo = 0;
    const Bar = self::Foo;
}

$reflectionEnum = new ReflectionEnum(Enum_::class);
var_dump($reflectionEnum->getCase('Foo'));
var_dump($reflectionEnum->getCase('Bar'));
var_dump($reflectionEnum->getCase('Baz'));

$reflectionEnum = new ReflectionEnum(IntEnum::class);
var_dump($reflectionEnum->getCase('Foo'));
var_dump($reflectionEnum->getCase('Bar'));
var_dump($reflectionEnum->getCase('Baz'));

?>
--EXPECT--
object(ReflectionEnumUnitCase)#2 (2) {
  ["name"]=>
  string(3) "Foo"
  ["class"]=>
  string(5) "Enum_"
}
bool(false)
bool(false)
object(ReflectionEnumBackedCase)#1 (2) {
  ["name"]=>
  string(3) "Foo"
  ["class"]=>
  string(7) "IntEnum"
}
bool(false)
bool(false)
