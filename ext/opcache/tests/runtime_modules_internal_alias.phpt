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
var_dump(module_run('phpt_opcache_internal_alias', fn() => [
    class_exists('_ZendTestClass', false),
    class_exists('_ZendTestClassAlias', false),
]));
?>
--EXPECT--
array(2) {
  [0]=>
  bool(true)
  [1]=>
  bool(true)
}
