<?php
function loadEnv(string $filePath): void {

    // Check .env file exists
    if (!file_exists($filePath)) {
        die('ERROR: .env file not found at: ' . $filePath);
    }

    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {

        // Skip comment lines starting with #
        if (str_starts_with(trim($line), '#')) {
            continue;
        }

        // Must contain = sign
        if (!str_contains($line, '=')) {
            continue;
        }

        // Split into KEY and VALUE on first = only
        [$key, $value] = explode('=', $line, 2);

        $key   = trim($key);
        $value = trim($value);

        // Remove surrounding quotes if present
        // e.g. DB_PASS="mypassword" or DB_PASS='mypassword'
        if (
            (str_starts_with($value, '"') && str_ends_with($value, '"')) ||
            (str_starts_with($value, "'") && str_ends_with($value, "'"))
        ) {
            $value = substr($value, 1, -1);
        }

        // Store in $_ENV and putenv
        $_ENV[$key] = $value;
        putenv("$key=$value");
    }
}

// Load the .env file — __DIR__ goes up one level from config/ to project root
loadEnv(__DIR__ . '/../.env');