<?php

namespace HPWebdeveloper\LaravelPayPocket\Services;

use Illuminate\Database\Eloquent\Model;

class PocketServices
{
    /**
     * Deposit an amount to the user's wallet of a specific type.
     *
     * @param $user
     * @param string $type
     * @param int|float $amount
     * @param ?string $notes
     *
     * @return bool
     */
    public function deposit(Model $user, string $type, int|float $amount, ?string $notes = null): bool
    {
        return $user->deposit($type, $amount, $notes);
    }

    /**
     * Pay the order value from the user's wallets.
     *
     * @param $user
     * @param int|float $orderValue
     * @param array $allowedWallets
     * @param ?string $notes
     *
     * @return void
     * @throws InsufficientBalanceException
     */
    public function pay(Model $user, $orderValue, array $allowedWallets = [], ?string $notes = null): void
    {
        $user->pay($orderValue, $allowedWallets, $notes);
    }

    /**
     * Get the balance of the user.
     *
     *
     * @param $user
     *
     * @return float|int
     */
    public function checkBalance(Model $user): int|float
    {
        return $user->walletBalance;
    }

    /**
     * Get the balance of a specific wallet type.
     *
     *
     * @param $user
     * @param string $type
     *
     * @return float|int
     */
    public function walletBalanceByType(Model $user, string $type): float|int
    {
        return $user->getWalletBalanceByType($type);
    }
}
