--TEST--
Runtime modules: include_once and get_included_files are per module
--FILE--
<?php
require_once __DIR__ . '/runtime_modules/file_fixture.inc';

$base = sys_get_temp_dir() . '/php_runtime_modules_phpt_' . getmypid();
mkdir($base);
$countFile = $base . '/count.php';
$classFile = $base . '/class.php';
file_put_contents($countFile, '<?php $GLOBALS["phpt_runtime_module_include_count"] = ($GLOBALS["phpt_runtime_module_include_count"] ?? 0) + 1;');
file_put_contents($classFile, '<?php class PhptIncludedClass { function id() { return PHPT_INCLUDED_ID; } }');

$m1 = 'phpt_include_m1';
$m2 = 'phpt_include_m2';
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$m1] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_167'
function () use ($countFile) { include_once $countFile; include_once $countFile; }
MODULE_ENTRY_167
, get_defined_vars(), __DIR__);
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$m2] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_168'
function () use ($countFile) { include_once $countFile; }
MODULE_ENTRY_168
, get_defined_vars(), __DIR__);
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$m1] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_169'
function () use ($countFile) { include_once $countFile; }
MODULE_ENTRY_169
, get_defined_vars(), __DIR__);
var_dump($GLOBALS['phpt_runtime_module_include_count']);
var_dump(in_array(realpath($countFile), get_included_files(), true));
var_dump(in_array(realpath($countFile), ($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$m1] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_170'
fn() => get_included_files()
MODULE_ENTRY_170
, get_defined_vars(), __DIR__), true));
var_dump(in_array(realpath($countFile), ($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$m2] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_171'
fn() => get_included_files()
MODULE_ENTRY_171
, get_defined_vars(), __DIR__), true));

($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$m1] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_172'
function () use ($classFile) { define('PHPT_INCLUDED_ID', 1); include_once $classFile; }
MODULE_ENTRY_172
, get_defined_vars(), __DIR__);
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$m2] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_173'
function () use ($classFile) { define('PHPT_INCLUDED_ID', 2); include_once $classFile; }
MODULE_ENTRY_173
, get_defined_vars(), __DIR__);
var_dump(($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$m1] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_174'
fn() => (new PhptIncludedClass())->id()
MODULE_ENTRY_174
, get_defined_vars(), __DIR__));
var_dump(($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$m2] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_175'
fn() => (new PhptIncludedClass())->id()
MODULE_ENTRY_175
, get_defined_vars(), __DIR__));

unlink($countFile);
unlink($classFile);
rmdir($base);
?>
--EXPECT--
int(2)
bool(false)
bool(true)
bool(true)
int(1)
int(2)
