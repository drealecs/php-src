--TEST--
Runtime modules: instanceof follows module class identity and dependency visibility
--FILE--
<?php
$a = 'phpt_instanceof_a';
$b = 'phpt_instanceof_b';
$dep = 'phpt_instanceof_dep';
$main = 'phpt_instanceof_main';

module_run($a, fn() => eval('class PhptInstanceofSameName {}'));
module_run($b, fn() => eval('class PhptInstanceofSameName {}'));

$aObject = module_run($a, fn() => new PhptInstanceofSameName());
$bObject = module_run($b, fn() => new PhptInstanceofSameName());

var_dump(module_run($a, fn() => [
    $aObject instanceof PhptInstanceofSameName,
    $bObject instanceof PhptInstanceofSameName,
]));

var_dump(module_run($b, fn() => [
    $aObject instanceof PhptInstanceofSameName,
    $bObject instanceof PhptInstanceofSameName,
]));

module_run($dep, function (): void {
    eval(<<<'PHP'
        interface PhptInstanceofDepInterface {}
        class PhptInstanceofDepBase {}
    PHP);
});

$mainObject = module_run($main, function () use ($dep): object {
    module_add_dependency($dep);
    eval(<<<'PHP'
        class PhptInstanceofChild extends PhptInstanceofDepBase implements PhptInstanceofDepInterface {}
    PHP);
    return new PhptInstanceofChild();
});

var_dump(module_run($main, fn() => [
    $mainObject instanceof PhptInstanceofChild,
    $mainObject instanceof PhptInstanceofDepBase,
    $mainObject instanceof PhptInstanceofDepInterface,
]));

var_dump($mainObject instanceof PhptInstanceofChild);
?>
--EXPECT--
array(2) {
  [0]=>
  bool(true)
  [1]=>
  bool(false)
}
array(2) {
  [0]=>
  bool(false)
  [1]=>
  bool(true)
}
array(3) {
  [0]=>
  bool(true)
  [1]=>
  bool(true)
  [2]=>
  bool(true)
}
bool(false)
