--TEST--
ReflectionEnumUnitCase::getBackingValue()
--FILE--
<?php

enum Enum_ {
    case Foo;
}

enum IntEnum: int {
    case Foo = 0;
}

enum StringEnum: string {
    case Foo = 'Foo';
}

var_dump((new ReflectionEnumUnitCase(Enum_::class, 'Foo'))->getBackingValue());
var_dump((new ReflectionEnumUnitCase(IntEnum::class, 'Foo'))->getBackingValue());
var_dump((new ReflectionEnumUnitCase(StringEnum::class, 'Foo'))->getBackingValue());

?>
--EXPECT--
NULL
int(0)
string(3) "Foo"
