<?php

namespace App\Services;

class MoneyConversionService
{
    public function normalizeCurrency(string $currencyCode): string
    {
        return strtoupper(trim($currencyCode));
    }

    public function toBaseMinor(
        int $amountMinor,
        string $currencyCode,
        string $baseCurrencyCode,
        float $exchangeRate
    ): int {
        if ($amountMinor <= 0) {
            return 0;
        }

        $normalizedCurrency = $this->normalizeCurrency($currencyCode);
        $normalizedBaseCurrency = $this->normalizeCurrency($baseCurrencyCode);

        if ($normalizedCurrency === $normalizedBaseCurrency) {
            return $amountMinor;
        }

        return (int) round($amountMinor * $exchangeRate);
    }
}
