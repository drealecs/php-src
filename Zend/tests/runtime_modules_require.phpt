--TEST--
Runtime modules: file entry has isolated locals, fixed ownership and normal require results
--FILE--
<?php
$root = RuntimeModule::current();
$first = new RuntimeModule();
$second = new RuntimeModule();
class RequireCaller {
    public function load(RuntimeModule $module): object {
        $callerLocal = 'not visible';
        return $module->require(__DIR__ . '/runtime_modules/entry.inc');
    }
    public function loadFactory(RuntimeModule $module): Closure {
        return $module->require(__DIR__ . '/runtime_modules/entry_factory.inc');
    }
}
$a = (new RequireCaller())->load($first);
$b = $second->require(__DIR__ . '/runtime_modules/entry.inc');
var_dump($a->context() === $first, $b->context() === $second);
var_dump(get_class($a) === get_class($b), $a instanceof $b);
var_dump(isset($entryLocal), RuntimeModule::current() === $root);
var_dump($first->require(__DIR__ . '/runtime_modules/repeat.inc'));
var_dump($first->require(__DIR__ . '/runtime_modules/repeat.inc'));
var_dump($GLOBALS['entry_runs']);
try {
    $first->require(__DIR__ . '/runtime_modules/throw.inc');
} catch (Exception $error) {
    echo $error->getMessage(), "\n";
}
var_dump(RuntimeModule::current() === $root);
$factory = (new RequireCaller())->loadFactory($first);
$reflection = new ReflectionFunction($factory);
var_dump($reflection->getClosureScopeClass(), $reflection->getClosureThis());
?>
--EXPECT--
array(0) {
}
bool(false)
array(0) {
}
bool(false)
bool(true)
bool(true)
bool(true)
bool(false)
bool(false)
bool(true)
int(1)
int(1)
int(2)
entry failed
bool(true)
NULL
NULL
