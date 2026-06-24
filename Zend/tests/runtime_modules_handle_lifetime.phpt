--TEST--
Runtime modules: canonical handles survive release and context follows normal and suspended calls
--FILE--
<?php
require_once __DIR__ . '/runtime_modules/file_fixture.inc';

$root = RuntimeModule::current();
$module = new RuntimeModule();
$weak = WeakReference::create($module);
$rootHelper = static fn() => RuntimeModule::current();
var_dump(($GLOBALS["module_fixture"])($module, <<<'MODULE_ENTRY_159'
fn() => $rootHelper()
MODULE_ENTRY_159
, get_defined_vars(), __DIR__) === $root);
var_dump(($GLOBALS["module_fixture"])($module, <<<'MODULE_ENTRY_160'
static fn() => RuntimeModule::current()
MODULE_ENTRY_160
, get_defined_vars(), __DIR__) === $module);
$factory = ($GLOBALS["module_fixture"])($module, <<<'MODULE_ENTRY_161'
static fn() => static fn() => RuntimeModule::current()
MODULE_ENTRY_161
, get_defined_vars(), __DIR__);
$generator = ($GLOBALS["module_fixture"])($module, <<<'MODULE_ENTRY_162'
static function () {
    yield RuntimeModule::current();
    yield RuntimeModule::current();
}
MODULE_ENTRY_162
, get_defined_vars(), __DIR__);
$fiber = ($GLOBALS["module_fixture"])($module, <<<'MODULE_ENTRY_163'
static fn() => new Fiber(static function () {
    Fiber::suspend(RuntimeModule::current());
    return RuntimeModule::current();
})
MODULE_ENTRY_163
, get_defined_vars(), __DIR__);
unset($module);
gc_collect_cycles();
var_dump($factory() === $weak->get());
var_dump($generator->current() === $weak->get());
$generator->next();
var_dump($generator->current() === $weak->get());
var_dump($fiber->start() === $weak->get());
$fiber->resume();
var_dump($fiber->getReturn() === $weak->get());
try {
    ($GLOBALS["module_fixture"])($weak->get(), <<<'MODULE_ENTRY_164'
static function (): void { throw new Exception('test'); }
MODULE_ENTRY_164
, get_defined_vars(), __DIR__);
} catch (Exception) {
    var_dump(RuntimeModule::current() === $root);
}
($GLOBALS["module_fixture"])($root, <<<'MODULE_ENTRY_165'
static fn() => var_dump(RuntimeModule::current() === $root)
MODULE_ENTRY_165
, get_defined_vars(), __DIR__);
?>
--EXPECT--
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
