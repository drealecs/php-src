--TEST--
Runtime modules: linked source class declarations retain an unlinked template
--FILE--
<?php
$a = 'phpt_linked_template_a';
$b = 'phpt_linked_template_b';

$factory = module_run($a, fn() => eval(<<<'PHP'
    interface PhptLinkedTemplateMarker {}
    return function (): object {
        return new class implements PhptLinkedTemplateMarker {};
    };
PHP));

module_run($b, fn() => eval('interface PhptLinkedTemplateMarker {}'));

$aObject = module_run($a, $factory);
$bObject = module_run($b, $factory);

var_dump(module_run($a, fn() => $aObject instanceof PhptLinkedTemplateMarker));
var_dump(module_run($b, fn() => $bObject instanceof PhptLinkedTemplateMarker));
var_dump(module_run($a, fn() => $bObject instanceof PhptLinkedTemplateMarker));
?>
--EXPECT--
bool(true)
bool(true)
bool(false)
