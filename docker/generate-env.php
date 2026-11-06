<?php

declare(strict_types=1);

// Run this file directly (on the host or in the README's bind-mounted container).
// Never send credentials to stdout or replace an existing environment file.
$path = dirname(__DIR__).'/.env.docker';
$temporaryPath = $path.'.tmp';
$temporaryFile = null;
$createdTemporaryFile = false;
umask(0077);

try {
    if (is_link($path) || is_link($temporaryPath)) {
        throw new RuntimeException('Refusing to use a symbolic link for Docker credentials.');
    }

    if (file_exists($path)) {
        if (! is_file($path) || ! is_readable($path)) {
            throw new RuntimeException('Existing .env.docker must be a readable regular file.');
        }

        $values = @parse_ini_file($path, false, INI_SCANNER_RAW);
        if ($values === false) {
            throw new RuntimeException('Existing .env.docker is malformed; restore or repair it without rotating database credentials.');
        }

        foreach (['APP_KEY', 'DB_PASSWORD', 'MYSQL_ROOT_PASSWORD', 'APP_PORT'] as $name) {
            if (! isset($values[$name]) || ! is_string($values[$name]) || trim($values[$name]) === '') {
                throw new RuntimeException('Existing .env.docker has a missing or empty '.$name.'; restore or repair it.');
            }
        }

        $key = str_starts_with($values['APP_KEY'], 'base64:')
            ? base64_decode(substr($values['APP_KEY'], 7), true)
            : false;
        if ($key === false || strlen($key) !== 32) {
            throw new RuntimeException('Existing .env.docker needs a valid 32-byte base64 APP_KEY; restore the original key.');
        }
        if (! ctype_digit($values['APP_PORT']) || (int) $values['APP_PORT'] < 1 || (int) $values['APP_PORT'] > 65535) {
            throw new RuntimeException('Existing .env.docker needs an APP_PORT between 1 and 65535.');
        }

        fwrite(STDOUT, "Existing .env.docker is valid; credentials were preserved.\n");
        exit(0);
    }

    // Exclusive creation prevents competing initializers sharing a temporary file.
    $temporaryFile = @fopen($temporaryPath, 'x+b');
    if ($temporaryFile === false) {
        throw new RuntimeException('Cannot create .env.docker.tmp. Check directory permissions or a previous incomplete initialization.');
    }
    $createdTemporaryFile = true;

    $contents = 'APP_KEY=base64:'.base64_encode(random_bytes(32))."\n"
        .'DB_PASSWORD='.bin2hex(random_bytes(24))."\n"
        .'MYSQL_ROOT_PASSWORD='.bin2hex(random_bytes(24))."\n"
        ."APP_PORT=8000\n";

    if (fwrite($temporaryFile, $contents) !== strlen($contents) || ! fflush($temporaryFile)) {
        throw new RuntimeException('Could not write Docker credentials completely.');
    }
    if (! @chmod($temporaryPath, 0600)) {
        throw new RuntimeException('Could not restrict Docker credential file permissions.');
    }
    fclose($temporaryFile);
    $temporaryFile = null;

    if (file_exists($path) || is_link($path) || ! @rename($temporaryPath, $path)) {
        throw new RuntimeException('Could not install .env.docker without replacing an existing file.');
    }
    $createdTemporaryFile = false;
    fwrite(STDOUT, "Created .env.docker with a random application key and independent database credentials.\n");
} catch (Throwable $exception) {
    if (is_resource($temporaryFile)) {
        fclose($temporaryFile);
    }
    if ($createdTemporaryFile) {
        @unlink($temporaryPath);
    }
    fwrite(STDERR, $exception->getMessage()."\n");
    exit(1);
}
