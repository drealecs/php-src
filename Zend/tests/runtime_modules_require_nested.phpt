--TEST--
Runtime modules: nested file entry, factories, path resolution and per-context once tracking
--FILE--
<?php
$root = RuntimeModule::current();
$first = new RuntimeModule();
$second = new RuntimeModule();
$GLOBALS['nested_module'] = $second;
$result = $first->require(__DIR__ . '/runtime_modules/entry_nested.inc');
var_dump($result[0] === $second, $result[1], $result[2]);
$factory = $first->require(__DIR__ . '/runtime_modules/entry_factory.inc');
var_dump($factory('configured')->value);
var_dump(class_exists('FactoryValue', false));

// Caller-relative lookup still works when the working directory differs.
$cwd = getcwd();
chdir(__DIR__ . '/..');
var_dump($first->require('runtime_modules/entry_context.inc')[0] === $first);
chdir($cwd);
$oldPath = set_include_path(__DIR__ . '/runtime_modules');
var_dump($first->require('entry_context.inc')[0] === $first);
set_include_path($oldPath);

$repeat = __DIR__ . '/runtime_modules/repeat.inc';
$first->require($repeat);
$files = $first->require(__DIR__ . '/runtime_modules/entry_include.inc');
var_dump(in_array(realpath($repeat), $files, true), $GLOBALS['entry_runs']);
$second->require(__DIR__ . '/runtime_modules/entry_include.inc');
var_dump($GLOBALS['entry_runs']);
require_once $repeat;
var_dump($GLOBALS['entry_runs'], RuntimeModule::current() === $root);
?>
--EXPECT--
string(5) "outer"
bool(true)
bool(true)
array(0) {
}
bool(false)
string(10) "configured"
bool(false)
bool(true)
bool(true)
bool(true)
int(1)
int(2)
int(3)
bool(true)
