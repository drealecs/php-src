--TEST--
Runtime modules: partial applications retain callable ownership and isolate caches
--FILE--
<?php
require_once __DIR__ . '/runtime_modules/file_fixture.inc';

$code = <<<'PHP'
class Item {}
function pick(Item $item, string $label = LABEL): Item {
    echo LABEL, ':', $label, "\n";
    return $item;
}
function label(): string { return LABEL; }
PHP;

foreach (['A', 'B'] as $module) {
    ($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$module] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_240'
function () use ($module, $code) {
        define('LABEL', $module);
        eval($code);
    }
MODULE_ENTRY_240
, get_defined_vars(), __DIR__);
}

$items = [];
$partials = [];
$withDefault = fn($fn) => $fn(?, ...);
$bound = fn($fn, $item) => $fn($item, ?);

foreach (['A', 'B', 'A'] as $module) {
    $item = ($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$module] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_241'
fn() => new Item()
MODULE_ENTRY_241
, get_defined_vars(), __DIR__);
    $fn = ($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$module] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_242'
fn() => function (Item $item, string $label = LABEL): Item {
    echo LABEL, ':', $label, "\n";
    return $item;
}
MODULE_ENTRY_242
, get_defined_vars(), __DIR__);
    $items[$module] = $item;
    $partials[] = [
        $module,
        $item,
        ($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$module] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_243'
fn() => pick(?, ?)
MODULE_ENTRY_243
, get_defined_vars(), __DIR__),
        $withDefault($fn),
        $bound($fn, $item),
        ($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$module] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_244'
fn() => call_user_func(?, ...)
MODULE_ENTRY_244
, get_defined_vars(), __DIR__),
    ];
}

foreach ($partials as [$module, $item, $namedPartial, $defaultPartial, $boundPartial, $internalPartial]) {
    var_dump($namedPartial($item, 'named') === $item);
    var_dump($defaultPartial($item) === $item);
    var_dump($boundPartial('bound') === $item);
    echo $internalPartial('label'), "\n";
    try {
        $defaultPartial($items[$module === 'A' ? 'B' : 'A']);
    } catch (TypeError) {
        echo "wrong module rejected\n";
    }
}
?>
--EXPECT--
A:named
bool(true)
A:A
bool(true)
A:bound
bool(true)
A
wrong module rejected
B:named
bool(true)
B:B
bool(true)
B:bound
bool(true)
B
wrong module rejected
A:named
bool(true)
A:A
bool(true)
A:bound
bool(true)
A
wrong module rejected
