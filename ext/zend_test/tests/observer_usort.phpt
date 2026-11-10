--TEST--
Observer: usort and uksort user callbacks retain balanced begin and end events
--EXTENSIONS--
zend_test
--INI--
zend_test.observer.enabled=1
zend_test.observer.show_output=1
zend_test.observer.observe_all=1
--FILE--
<?php
function compare($a, $b): int { return $a <=> $b; }
foreach (['usort', 'usort', 'uksort'] as $sort) {
    $items = [2 => 2, 1 => 1];
    $sort($items, 'compare');
    foreach ($items as $item) { echo $item; }
    echo "\n";
}
?>
--EXPECTF--
<!-- init '%s%eobserver_usort.php' -->
<file '%s%eobserver_usort.php'>
  <!-- init usort() -->
  <usort>
    <!-- init compare() -->
    <compare>
    </compare>
  </usort>
12
  <usort>
    <compare>
    </compare>
  </usort>
12
  <!-- init uksort() -->
  <uksort>
    <compare>
    </compare>
  </uksort>
12
</file '%s%eobserver_usort.php'>
