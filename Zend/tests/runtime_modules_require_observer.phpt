--TEST--
Runtime modules: observers see balanced file entry and method calls
--EXTENSIONS--
zend_test
--INI--
zend_test.observer.enabled=1
zend_test.observer.show_output=1
zend_test.observer.observe_all=1
--FILE--
<?php
$module = new RuntimeModule();
$module->require(__DIR__ . '/runtime_modules/entry_value.inc');
$module->require(__DIR__ . '/runtime_modules/entry_value.inc');
echo "DONE\n";
?>
--EXPECTF--
<!-- init '%s%eruntime_modules_require_observer.php' -->
<file '%s%eruntime_modules_require_observer.php'>
  <!-- init RuntimeModule::__construct() -->
  <RuntimeModule::__construct>
  </RuntimeModule::__construct>
  <!-- init RuntimeModule::require() -->
  <RuntimeModule::require>
    <!-- init '%s%eentry_value.inc' -->
    <file '%s%eentry_value.inc'>
    </file '%s%eentry_value.inc'>
  </RuntimeModule::require>
  <RuntimeModule::require>
    <!-- init '%s%eentry_value.inc' -->
    <file '%s%eentry_value.inc'>
    </file '%s%eentry_value.inc'>
  </RuntimeModule::require>
DONE
</file '%s%eruntime_modules_require_observer.php'>
