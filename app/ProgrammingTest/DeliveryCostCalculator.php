<?php

namespace ProgrammingTest;

class DeliveryCostCalculator
{
    public static function calculateCost(Item $item): Item
    {
        $longestDimension = max($item->width, $item->length, $item->height);
        if ($longestDimension < 10) {
            $item->cost = 3;
            $item->type = ItemType::Small;
        } else if ($longestDimension < 50) {
            $item->cost = 8;
            $item->type = ItemType::Medium;
        } else if ($longestDimension < 100) {
            $item->cost = 15;
            $item->type = ItemType::Large;
        } else {
            $item->cost = 25;
            $item->type = ItemType::XLarge;
        }
        return $item;
    }
}
