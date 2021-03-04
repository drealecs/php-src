--TEST--
ReflectionEnumBackedCase::getBackingValue()
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

try {
    var_dump((new ReflectionEnumBackedCase(Enum_::class, 'Foo'))->getBackingValue());
} catch (ReflectionException $e) {
    echo $e->getMessage() . "\n";
}

var_dump((new ReflectionEnumBackedCase(IntEnum::class, 'Foo'))->getBackingValue());
var_dump((new ReflectionEnumBackedCase(StringEnum::class, 'Foo'))->getBackingValue());

?>
--EXPECT--
Enum case Enum_::Foo is not a backed case
int(0)
string(3) "Foo"
