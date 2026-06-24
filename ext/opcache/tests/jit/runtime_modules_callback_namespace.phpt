--TEST--
JIT runtime modules: namespace fallback caches follow visibility changes inside callbacks
--EXTENSIONS--
opcache
--INI--
opcache.enable_cli=1
opcache.file_update_protection=0
opcache.jit=function
opcache.jit_buffer_size=16M
--FILE--
<?php
namespace {
require_once __DIR__ . '/../../../../Zend/tests/runtime_modules/file_fixture.inc';

    const CALLBACK_VALUE = 'root';
    function callback_function(): string { return 'root'; }
}
namespace ModuleCallback {
    function probe(): array {
        $seen = [];
        for ($i = 0; $i < 2; $i++) {
            $seen[] = [strlen('abc'), CALLBACK_VALUE, callback_function()];
            if ($i === 0) {
                trigger_error('import', E_USER_NOTICE);
            }
        }
        return [$seen, (new VisibleClass())->value(), VisibleClass::VALUE];
    }
}
namespace {
    $root = RuntimeModule::current();
    $dependency = new RuntimeModule();
    ($GLOBALS["module_fixture"])($dependency, <<<'MODULE_ENTRY_380'
static fn() => eval(<<<'PHP'
        namespace ModuleCallback;
        const CALLBACK_VALUE = 'module';
        function callback_function(): string { return 'module'; }
        function strlen(string $value): int { return 42; }
        class VisibleClass {
            public const VALUE = 'class';
            public function value(): string { return self::VALUE; }
        }
    PHP)
MODULE_ENTRY_380
, get_defined_vars(), __DIR__);
    set_error_handler(static function () use ($root, $dependency): bool {
        $root->addDependency($dependency);
        return true;
    });
    var_dump(ModuleCallback\probe());
    restore_error_handler();
}
?>
--EXPECT--
array(3) {
  [0]=>
  array(2) {
    [0]=>
    array(3) {
      [0]=>
      int(3)
      [1]=>
      string(4) "root"
      [2]=>
      string(4) "root"
    }
    [1]=>
    array(3) {
      [0]=>
      int(42)
      [1]=>
      string(6) "module"
      [2]=>
      string(6) "module"
    }
  }
  [1]=>
  string(5) "class"
  [2]=>
  string(5) "class"
}
