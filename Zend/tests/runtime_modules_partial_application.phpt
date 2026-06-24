--TEST--
Runtime modules: partial applications retain callable ownership and isolate caches
--FILE--
<?php
$code = <<<'PHP'
class Item {}
function pick(Item $item, string $label = LABEL): Item {
    echo LABEL, ':', $label, "\n";
    return $item;
}
function label(): string { return LABEL; }
PHP;

foreach (['A', 'B'] as $module) {
    module_run($module, function () use ($module, $code) {
        define('LABEL', $module);
        eval($code);
    });
}

$items = [];
$partials = [];
$named = fn() => pick(?, ?);
$closure = fn() => function (Item $item, string $label = LABEL): Item {
    echo LABEL, ':', $label, "\n";
    return $item;
};
$withDefault = fn($fn) => $fn(?, ...);
$bound = fn($fn, $item) => $fn($item, ?);
$internal = fn() => call_user_func(?, ...);

foreach (['A', 'B', 'A'] as $module) {
    $item = module_run($module, fn() => new Item());
    $fn = module_run($module, $closure);
    $items[$module] = $item;
    $partials[] = [
        $module,
        $item,
        module_run($module, $named),
        $withDefault($fn),
        $bound($fn, $item),
        module_run($module, $internal),
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
