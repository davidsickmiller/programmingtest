<?php

class DeliveryCostCalculator
{
    public static function calculateCost(float $width, float $length, float $height): int
    {
        $longestDimension = max($width, $length, $height);
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
