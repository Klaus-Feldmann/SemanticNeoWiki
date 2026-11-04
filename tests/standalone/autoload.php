<?php

declare(strict_types=1);

$vendor = getenv('NEOWIKI_TEST_VENDOR') ?: __DIR__ . '/../../publication-tests/dependencies/vendor/autoload.php';
if (is_file($vendor)) { require_once $vendor; }


spl_autoload_register(static function (string $class): void {
    $testPrefix = 'ProfessionalWiki\\NeoWiki\\Tests\\';
    if (str_starts_with($class, $testPrefix)) {
        $file = __DIR__ . '/../phpunit/' . str_replace('\\', '/', substr($class, strlen($testPrefix))) . '.php';
        if (is_file($file)) { require $file; }
        return;
    }
    $prefix = 'ProfessionalWiki\\NeoWiki\\';
    if (str_starts_with($class, $prefix)) {
        $file = __DIR__ . '/../../src/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
        if (is_file($file)) {
            require $file;
        }
    }
});
