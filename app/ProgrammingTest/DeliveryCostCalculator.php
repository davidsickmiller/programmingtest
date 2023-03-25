<?php

namespace ProgrammingTest;

class DeliveryCostCalculator
{
    private const OVER_WEIGHT_FEE_PER_KG = 2;

    // PHP 8.1 doesn't allow enum types as keys of arrays, so use a function to map
    private static function getLimitByType(ItemType $type)
    {
        if ($type === ItemType::Small) {
            return 1;
        }
        if ($type === ItemType::Medium) {
            return 3;
        }
        if ($type === ItemType::Large) {
            return 6;
        }
        return 10;
    }

    private static function calculateHeavyParcelCost(Item $item): int
    {
        return max(50, ceil($item->weight));
    }

    /**
     * @param Item[] $items
     * @return array - 'items', an array of Items
     *               - 'totalCost', an int for the cost of all items
     */
    public static function calculateCost(array $items, ShippingType $shippingType = ShippingType::Standard): array
    {
        $totalCost = 0;
        foreach ($items as $item) {
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
            $weightLimit = self::getLimitByType($item->type);
            if ($item->weight > $weightLimit) {
                $item->cost += self::OVER_WEIGHT_FEE_PER_KG * ceil($item->weight - $weightLimit);
            }

            // Consider if we should switch to heavy parcel pricing
            $heavyParcelCost = self::calculateHeavyParcelCost($item);
            if ($heavyParcelCost < $item->cost) {
                $item->type = ItemType::Heavy;
                $item->cost = $heavyParcelCost;
            }

            $totalCost += $item->cost;
        }
        if ($shippingType === ShippingType::Speedy) {
            $items[] = new Item(null, null, null, null, ItemType::SpeedyShipping, $totalCost);
            $totalCost += $totalCost;
        }

        return ['items' => $items, 'totalCost' => $totalCost];
    }
}
