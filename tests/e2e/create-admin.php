<?php

use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

require dirname(__DIR__, 2).'/vendor/autoload.php';
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$database = config('database.connections.mysql');
if (
    ! $app->environment('local', 'testing')
    || config('database.default') !== 'mysql'
    || $database['database'] !== 'ecommerce_platform_e2e'
    || ! in_array($database['host'], ['localhost', '127.0.0.1'], true)
    || ! empty($database['url'])
) {
    throw new RuntimeException('This fixture requires a dedicated local ecommerce_platform_e2e MySQL database.');
}

$input = Validator::make([
    'email' => Str::lower((string) getenv('E2E_ADMIN_EMAIL')),
    'password' => getenv('E2E_ADMIN_PASSWORD'),
], [
    'email' => ['required', 'email:rfc', 'ends_with:@example.test'],
    'password' => ['required', 'string', 'min:12', 'max:72'],
])->validate();

$user = User::firstOrNew(['email' => $input['email']]);
$user->forceFill(['name' => 'Browser Test Admin', 'password' => $input['password'], 'is_admin' => true])->save();

echo "Browser fixture administrator ready.\n";
