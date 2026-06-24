--TEST--
Runtime modules: invoking an anonymous-class factory from another module retains its owner
--FILE--
<?php
require_once __DIR__ . '/runtime_modules/file_fixture.inc';

$a = 'phpt_linked_template_a';
$b = 'phpt_linked_template_b';

$factory = ($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$a] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_204'
fn() => eval(<<<'PHP'
    interface PhptLinkedTemplateMarker {}
    return function (): object {
        return new class implements PhptLinkedTemplateMarker {};
    };
PHP)
MODULE_ENTRY_204
, get_defined_vars(), __DIR__);

($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$b] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_205'
fn() => eval('interface PhptLinkedTemplateMarker {}')
MODULE_ENTRY_205
, get_defined_vars(), __DIR__);

$aObject = ($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$a] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_206'
$factory
MODULE_ENTRY_206
, get_defined_vars(), __DIR__);
$bObject = ($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$b] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_207'
$factory
MODULE_ENTRY_207
, get_defined_vars(), __DIR__);

var_dump(($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$a] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_208'
fn() => $aObject instanceof PhptLinkedTemplateMarker
MODULE_ENTRY_208
, get_defined_vars(), __DIR__));
var_dump(($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$b] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_209'
fn() => $bObject instanceof PhptLinkedTemplateMarker
MODULE_ENTRY_209
, get_defined_vars(), __DIR__));
var_dump(($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$a] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_210'
fn() => $bObject instanceof PhptLinkedTemplateMarker
MODULE_ENTRY_210
, get_defined_vars(), __DIR__));
?>
--EXPECT--
bool(true)
bool(false)
bool(true)
