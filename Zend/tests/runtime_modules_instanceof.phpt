--TEST--
Runtime modules: instanceof follows module class identity and dependency visibility
--FILE--
<?php
require_once __DIR__ . '/runtime_modules/file_fixture.inc';

$a = 'phpt_instanceof_a';
$b = 'phpt_instanceof_b';
$dep = 'phpt_instanceof_dep';
$main = 'phpt_instanceof_main';

($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$main] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_194'
fn() => \RuntimeModule::current()->addDependency($GLOBALS["runtime_modules"][$dep] ??= new \RuntimeModule())
MODULE_ENTRY_194
, get_defined_vars(), __DIR__);

($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$a] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_195'
fn() => eval('class PhptInstanceofSameName {}')
MODULE_ENTRY_195
, get_defined_vars(), __DIR__);
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$b] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_196'
fn() => eval('class PhptInstanceofSameName {}')
MODULE_ENTRY_196
, get_defined_vars(), __DIR__);

$aObject = ($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$a] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_197'
fn() => new PhptInstanceofSameName()
MODULE_ENTRY_197
, get_defined_vars(), __DIR__);
$bObject = ($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$b] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_198'
fn() => new PhptInstanceofSameName()
MODULE_ENTRY_198
, get_defined_vars(), __DIR__);

var_dump(($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$a] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_199'
fn() => [
    $aObject instanceof PhptInstanceofSameName,
    $bObject instanceof PhptInstanceofSameName,
]
MODULE_ENTRY_199
, get_defined_vars(), __DIR__));

var_dump(($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$b] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_200'
fn() => [
    $aObject instanceof PhptInstanceofSameName,
    $bObject instanceof PhptInstanceofSameName,
]
MODULE_ENTRY_200
, get_defined_vars(), __DIR__));

($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$dep] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_201'
function (): void {
    eval(<<<'PHP'
        interface PhptInstanceofDepInterface {}
        class PhptInstanceofDepBase {}
    PHP);
}
MODULE_ENTRY_201
, get_defined_vars(), __DIR__);

$mainObject = ($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$main] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_202'
function (): object {
    eval(<<<'PHP'
        class PhptInstanceofChild extends PhptInstanceofDepBase implements PhptInstanceofDepInterface {}
    PHP);
    return new PhptInstanceofChild();
}
MODULE_ENTRY_202
, get_defined_vars(), __DIR__);

var_dump(($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$main] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_203'
fn() => [
    $mainObject instanceof PhptInstanceofChild,
    $mainObject instanceof PhptInstanceofDepBase,
    $mainObject instanceof PhptInstanceofDepInterface,
]
MODULE_ENTRY_203
, get_defined_vars(), __DIR__));

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
