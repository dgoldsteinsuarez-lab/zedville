<?php

namespace App\Services\MoneyFactory;

/**
 * A small repeatable random generator (xorshift32): the same seed always gives
 * the same numbers, so a banknote's story or a task's numbers can be worked out
 * again without storing them - and without touching PHP's global mt_rand state.
 */
class SeededRandom
{
    private int $state;

    public function __construct(int $seed)
    {
        $this->state = ($seed & 0xFFFFFFFF) ?: 0x9E3779B9;
    }

    public function next(): int
    {
        $x = $this->state;
        $x ^= ($x << 13) & 0xFFFFFFFF;
        $x ^= $x >> 17;
        $x ^= ($x << 5) & 0xFFFFFFFF;

        return $this->state = $x & 0xFFFFFFFF;
    }

    public function between(int $min, int $max): int
    {
        return $min + $this->next() % ($max - $min + 1);
    }

    public function shuffle(array $items): array
    {
        $items = array_values($items);
        for ($i = count($items) - 1; $i > 0; $i--) {
            $j = $this->between(0, $i);
            [$items[$i], $items[$j]] = [$items[$j], $items[$i]];
        }

        return $items;
    }
}
