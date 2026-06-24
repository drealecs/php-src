--TEST--
Runtime modules: include_once and get_included_files are per module
--FILE--
<?php
$base = sys_get_temp_dir() . '/php_runtime_modules_phpt_' . getmypid();
mkdir($base);
$countFile = $base . '/count.php';
$classFile = $base . '/class.php';
file_put_contents($countFile, '<?php $GLOBALS["phpt_runtime_module_include_count"] = ($GLOBALS["phpt_runtime_module_include_count"] ?? 0) + 1;');
file_put_contents($classFile, '<?php class PhptIncludedClass { function id() { return PHPT_INCLUDED_ID; } }');

$m1 = 'phpt_include_m1';
$m2 = 'phpt_include_m2';
module_run($m1, function () use ($countFile) { include_once $countFile; include_once $countFile; });
module_run($m2, function () use ($countFile) { include_once $countFile; });
module_run($m1, function () use ($countFile) { include_once $countFile; });
var_dump($GLOBALS['phpt_runtime_module_include_count']);
var_dump(in_array(realpath($countFile), get_included_files(), true));
var_dump(in_array(realpath($countFile), module_run($m1, fn() => get_included_files()), true));
var_dump(in_array(realpath($countFile), module_run($m2, fn() => get_included_files()), true));

module_run($m1, function () use ($classFile) { define('PHPT_INCLUDED_ID', 1); include_once $classFile; });
module_run($m2, function () use ($classFile) { define('PHPT_INCLUDED_ID', 2); include_once $classFile; });
var_dump(module_run($m1, fn() => (new PhptIncludedClass())->id()));
var_dump(module_run($m2, fn() => (new PhptIncludedClass())->id()));

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
