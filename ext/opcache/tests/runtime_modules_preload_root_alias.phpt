--TEST--
Runtime modules: preloaded root aliases remain root-local
--INI--
opcache.enable=1
opcache.enable_cli=1
opcache.preload={PWD}/runtime_modules_preload_root_alias.inc
--EXTENSIONS--
opcache
--SKIPIF--
<?php
if (PHP_OS_FAMILY == 'Windows') die('skip Preloading is not supported on Windows');
?>
--FILE--
<?php
$alias = 'PhptRuntimeModulesPreloadRootAlias';
$module = 'phpt_preload_root_alias';

var_dump(interface_exists($alias, false));
var_dump(module_run($module, fn() => interface_exists($alias, false)));

module_run($module, fn() => eval('interface PhptRuntimeModulesPreloadRootAlias {}'));
var_dump(module_run($module, fn() => interface_exists($alias, false)));
?>
--EXPECT--
bool(true)
bool(false)
bool(true)
