--TEST--
Runtime modules: cross-module callbacks preserve called scope and bound objects
--FILE--
<?php
require_once __DIR__ . '/runtime_modules/file_fixture.inc';

class ModuleScopeBase {
    public static function identify(): string {
        return static::class;
    }

    public static function makeClosure(): Closure {
        return static fn(): string => static::class;
    }

    public function instanceClosure(): Closure {
        return fn(): object => $this;
    }
}
class ModuleScopeChild extends ModuleScopeBase {}

foreach ([ModuleScopeChild::identify(...), ModuleScopeChild::makeClosure()] as $callback) {
    var_dump($callback(), ($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['called-scope'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_27'
$callback
MODULE_ENTRY_27
, get_defined_vars(), __DIR__), $callback());
}
$object = new ModuleScopeChild();
$callback = $object->instanceClosure();
var_dump(($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['called-scope'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_28'
$callback
MODULE_ENTRY_28
, get_defined_vars(), __DIR__) === $object);
?>
--EXPECT--
string(16) "ModuleScopeChild"
string(16) "ModuleScopeChild"
string(16) "ModuleScopeChild"
string(16) "ModuleScopeChild"
string(16) "ModuleScopeChild"
string(16) "ModuleScopeChild"
bool(true)
