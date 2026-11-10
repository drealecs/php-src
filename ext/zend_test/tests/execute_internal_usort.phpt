--TEST--
Native sorting callbacks respect zend_execute_internal
--EXTENSIONS--
zend_test
--INI--
zend_test.observer.execute_internal=1
--FILE--
<?php
foreach (['strcmp', strcmp(...)] as $callback) {
    $items = ['b', 'a'];
    usort($items, $callback);
    echo $items[0], $items[1], "\n";
}
?>
--EXPECT--
<!-- internal enter usort() -->
  <!-- internal enter strcmp() -->
  <!-- internal leave strcmp() -->
<!-- internal leave usort() -->
ab
<!-- internal enter usort() -->
  <!-- internal enter strcmp() -->
  <!-- internal leave strcmp() -->
<!-- internal leave usort() -->
ab
