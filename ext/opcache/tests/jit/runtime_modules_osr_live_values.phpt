--TEST--
JIT runtime modules: OSR reloads live root values and falls back after a dependency import
--EXTENSIONS--
opcache
--INI--
opcache.enable_cli=1
opcache.file_update_protection=0
opcache.jit=1235
opcache.jit_buffer_size=16M
opcache.jit_hot_func=0
opcache.jit_hot_loop=1
--FILE--
<?php
require_once __DIR__ . '/../../../../Zend/tests/runtime_modules/file_fixture.inc';
function rootLoop(int $iterations = 3): int {
    $sum = 0;
    for ($i = 0; $i < $iterations; $i++) {
        $sum += $i;
    }
    return $sum;
}
function dependencyLoop(int $iterations = 3): array {
    $sum = 0;
    for ($i = 0; $i < $iterations; $i++) {
        $sum += $i;
    }
    return [$sum, OSR_IMPORTED];
}
var_dump(rootLoop(), rootLoop());
$dependency = new RuntimeModule();
($GLOBALS['module_fixture'])($dependency, 'fn() => define("OSR_IMPORTED", "visible")', [], __DIR__);
RuntimeModule::current()->addDependency($dependency);
var_dump(dependencyLoop(), rootLoop());
?>
--EXPECT--
int(3)
int(3)
array(2) {
  [0]=>
  int(3)
  [1]=>
  string(7) "visible"
}
int(3)
