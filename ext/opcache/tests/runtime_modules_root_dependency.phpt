--TEST--
Runtime modules: opcache bypasses module-sensitive root compilation
--EXTENSIONS--
opcache
--INI--
opcache.enable=1
opcache.enable_cli=1
opcache.file_update_protection=0
--FILE--
<?php
$file = __DIR__ . '/runtime_modules_root_dependency.inc';
$symbolFile = __DIR__ . '/runtime_modules_root_symbols.inc';
file_put_contents($symbolFile, '<?php function phpt_opcache_root_function(): string { return "root"; } class PhptOpcacheRootClass { public static int $value = 42; }');
include $symbolFile;
var_dump(opcache_is_script_cached($symbolFile));
var_dump(function_exists('phpt_opcache_root_function'));
var_dump(phpt_opcache_root_function());
var_dump(class_exists(PhptOpcacheRootClass::class, false));
var_dump((new ReflectionClass(PhptOpcacheRootClass::class))->getName());
var_dump(PhptOpcacheRootClass::$value);

file_put_contents($file, <<<'PHP'
<?php
class PhptOpcacheRootChild extends PhptOpcacheDependencyBase {}
PHP);

$dependency = 'phpt_opcache_root_dependency';
module_run($dependency, fn() => eval('class PhptOpcacheDependencyBase {}'));
module_add_dependency($dependency);

var_dump(@opcache_compile_file($file));
var_dump(opcache_is_script_cached($file));
include $file;
var_dump(is_subclass_of(PhptOpcacheRootChild::class, 'PhptOpcacheDependencyBase'));
var_dump(opcache_is_script_cached($file));
?>
--CLEAN--
<?php
@unlink(__DIR__ . '/runtime_modules_root_dependency.inc');
@unlink(__DIR__ . '/runtime_modules_root_symbols.inc');
?>
--EXPECT--
bool(true)
bool(true)
string(4) "root"
bool(true)
string(20) "PhptOpcacheRootClass"
int(42)
bool(false)
bool(false)
bool(true)
bool(false)
