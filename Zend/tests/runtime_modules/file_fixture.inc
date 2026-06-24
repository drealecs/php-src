<?php
/* Test-only source fixtures: compile each entry through the file API, not by rebinding a Closure. */
$GLOBALS['module_fixture_files'] = [];
register_shutdown_function(static function (): void {
    foreach ($GLOBALS['module_fixture_files'] as $file) {
        @unlink($file);
    }
});
$GLOBALS['module_fixture'] = static function (RuntimeModule $module, string $source, array $bindings, string $directory): mixed {
    $file = tempnam($directory, 'module_entry_');
    $GLOBALS['module_fixture_files'][$file] = $file;
    $GLOBALS['module_fixture_bindings'][$file] = $bindings;
    file_put_contents($file, '<?php extract($GLOBALS["module_fixture_bindings"][' . var_export($file, true) . '], EXTR_REFS); return (' . $source . ')();');
    try {
        return $module->require($file);
    } finally {
        unset($GLOBALS['module_fixture_bindings'][$file], $GLOBALS['module_fixture_files'][$file]);
        unlink($file);
    }
};
