--TEST--
Runtime modules: persistent internal aliases remain visible with opcache
--EXTENSIONS--
opcache
zend_test
--INI--
opcache.enable=1
opcache.enable_cli=1
--FILE--
<?php
require_once __DIR__ . '/../../../Zend/tests/runtime_modules/file_fixture.inc';

var_dump(($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['phpt_opcache_internal_alias'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_396'
fn() => [
    class_exists('_ZendTestClass', false),
    class_exists('_ZendTestClassAlias', false),
]
MODULE_ENTRY_396
, get_defined_vars(), __DIR__));
?>
--EXPECT--
array(2) {
  [0]=>
  bool(true)
  [1]=>
  bool(true)
}
