--TEST--
usort() keeps deprecations and called scope for user comparators
--FILE--
<?php
#[Deprecated]
function deprecatedCompare($a, $b): int { return $a <=> $b; }

class SortBase {
    public static function compare($a, $b): int {
        if (static::class !== SortChild::class) {
            throw new Exception('Wrong called scope ' . static::class);
        }
        return $a <=> $b;
    }
}
class SortChild extends SortBase {}

foreach (['deprecatedCompare', [SortChild::class, 'compare'], SortChild::compare(...)] as $callback) {
    $items = [2, 1];
    usort($items, $callback);
    echo implode(',', $items), "\n";
}
?>
--EXPECTF--
Deprecated: Function deprecatedCompare() is deprecated in %s on line %d
1,2
1,2
1,2
