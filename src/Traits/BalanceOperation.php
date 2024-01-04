<?php

namespace HPWebdeveloper\LaravelPayPocket\Traits;

use Illuminate\Support\Str;

trait BalanceOperation
{
    protected $createdLog;

    /**
     * Check if Balance is more than zero.
     *
     * @return bool
     */
    public function hasBalance(): bool
    {
        return $this->balance > 0;
    }

    /**
     * Decrement Balance and create a log entry.
     *
     * @param int|float $value
     * @param ?string $notes
     *
     * @return void
     */
    public function decrementAndCreateLog($value, ?string $notes = null): void
    {
        $this->createLog('dec', $value, $notes);
        $this->decrement('balance', $value);
    }

    /**
     * Increment Balance and create a log entry.
     *
     * @param int|float $value
     * @param ?string $notes
     *
     * @return void
     */
    public function incrementAndCreateLog($value, ?string $notes = null): void
    {
        $this->createLog('inc', $value, $notes);
        $this->increment('balance', $value);
    }

    /**
     * Create a new log record
     *
     * @param string $logType
     * @param int|float $value
     * @param ?string $notes
     *
     * @return void
     */
    protected function createLog($logType, $value, ?string $notes = null): void
    {
        $currentBalance = $this->balance ?? 0;

        $newBalance = $logType === 'dec' ? $currentBalance - $value : $currentBalance + $value;

        $refGen = config('pay-pocket.log_reference_generator', [Str::class, 'random', [12]]);

        $reference = config('pay-pocket.log_reference_prefix', '');

        $reference .= isset($refGen[0], $refGen[1])
            ? $refGen[0]::{$refGen[1]}(...$refGen[2] ?? [])
            : Str::random(config('pay-pocket.log_reference_length', 12));

        $this->createdLog = $this->logs()->create([
            'wallet_name' => $this->type->value,
            'from' => $currentBalance,
            'to' => $newBalance,
            'ip' => \Request::ip(),
            'type' => $logType,
            'value' => $value,
            'notes' => $notes,
            'reference' => $reference
        ]);

        $this->createdLog->changeStatus('Done');
    }
}
