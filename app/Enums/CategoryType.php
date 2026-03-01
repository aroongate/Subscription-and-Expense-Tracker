<?php

namespace App\Enums;

enum CategoryType: string
{
    case Expense = 'expense';
    case Subscription = 'subscription';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $type): string => $type->value, self::cases());
    }
}
