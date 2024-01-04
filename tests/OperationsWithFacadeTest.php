<?php

use HPWebdeveloper\LaravelPayPocket\Facades\LaravelPayPocket;
use HPWebdeveloper\LaravelPayPocket\Models\WalletsLog;
use HPWebdeveloper\LaravelPayPocket\Tests\Models\User;

it('can test', function () {
    expect(true)->toBeTrue();
});

test('user can deposit fund', function () {

    $user = User::factory()->create();

    $type = 'wallet_2';

    LaravelPayPocket::deposit($user, $type, 234.56);

    expect(LaravelPayPocket::walletBalanceByType($user, 'wallet_2'))->toBeFloat(234.56);

    expect(LaravelPayPocket::checkBalance($user))->toBeFloat(234.56);
});

test('user can deposit two times', function () {

    $user = User::factory()->create();

    $type = 'wallet_2';

    LaravelPayPocket::deposit($user, $type, 234.56);

    LaravelPayPocket::deposit($user, $type, 789.12);

    expect(LaravelPayPocket::walletBalanceByType($user, 'wallet_2'))->toBeFloat(1023.68);

    expect(LaravelPayPocket::checkBalance($user))->toBeFloat(1023.68);
});

test('user can pay order', function () {

    $user = User::factory()->create();

    $type = 'wallet_2';

    LaravelPayPocket::deposit($user, $type, 234.56);

    LaravelPayPocket::pay($user, 100.16);

    expect(LaravelPayPocket::walletBalanceByType($user, 'wallet_2'))->toBeFloat(134.40);

    expect(LaravelPayPocket::checkBalance($user))->toBeFloat(134.40);
});

test('user can deposit two times and pay an order', function () {

    $user = User::factory()->create();

    $type = 'wallet_1';

    LaravelPayPocket::deposit($user, $type, 234.11);

    expect(LaravelPayPocket::walletBalanceByType($user, 'wallet_1'))->toBeFloat(234.11);

    $type = 'wallet_2';
    LaravelPayPocket::deposit($user, $type, 100.12);

    expect(LaravelPayPocket::walletBalanceByType($user, 'wallet_2'))->toBeFloat(100.12);

    LaravelPayPocket::pay($user, 100);

    expect(LaravelPayPocket::walletBalanceByType($user, 'wallet_1'))->toBeFloat(134.11);

    expect(LaravelPayPocket::checkBalance($user))->toBeFloat(234.33);
});

test('user pay from two wallets', function () {

    $user = User::factory()->create();

    $type = 'wallet_1';

    LaravelPayPocket::deposit($user, $type, 234.11);

    expect(LaravelPayPocket::walletBalanceByType($user, 'wallet_1'))->toBeFloat(234.11);

    $type = 'wallet_2';
    LaravelPayPocket::deposit($user, $type, 100.12);

    expect(LaravelPayPocket::walletBalanceByType($user, 'wallet_2'))->toBeFloat(100.12);

    LaravelPayPocket::pay($user, 334.11);

    expect(LaravelPayPocket::walletBalanceByType($user, 'wallet_1'))->toBe(0);

    expect(LaravelPayPocket::walletBalanceByType($user, 'wallet_2'))->toBeFloat(0.12);

    expect(LaravelPayPocket::checkBalance($user))->toBeFloat(0.12);
});

test('only the choosen wallet will be charged.', function () {

    $user = User::factory()->create();

    $type = 'wallet_1';

    LaravelPayPocket::deposit($user, $type, 234.56);
    LaravelPayPocket::pay($user, 234.56, [$type]);

    $last = $user->wallets()
        ->where('type', \App\Enums\WalletEnums::WALLET1)
        ->first()
        ->logs()->latest()->first();

    expect($last->value)->toBeFloat(234.56);
});

test('description can be added during deposit', function () {
    $user = User::factory()->create();

    $type = 'wallet_2';

    $description = \Illuminate\Support\Str::random();
    $user->deposit($type, 234.56, $description);

    expect(WalletsLog::where('notes', $description)->exists())->toBe(true);
});

test('description can be added during payment', function () {
    $user = User::factory()->create();

    $type = 'wallet_2';

    $description = \Illuminate\Support\Str::random();
    LaravelPayPocket::deposit($user, $type, 234.56);
    LaravelPayPocket::pay($user, 234.56, [$type], $description);

    expect(WalletsLog::where('notes', $description)->exists())->toBe(true);
});
