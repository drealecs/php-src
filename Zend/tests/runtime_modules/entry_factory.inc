<?php
class FactoryValue {
    public function __construct(public string $value) {}
}
return static fn(string $value): FactoryValue => new FactoryValue($value);
