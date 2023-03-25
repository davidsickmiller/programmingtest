<?php

namespace ProgrammingTest;

class DeliveryCostCalculator
{
    public static function calculateCost(Item $item): int
    {
        $longestDimension = max($item->width, $item->length, $item->height);
        if ($longestDimension < 10) {
            return 3;
        }
        if ($longestDimension < 50) {
            return 8;
        }
        if ($longestDimension < 100) {
            return 15;
        }
        return 25;
    }
}
