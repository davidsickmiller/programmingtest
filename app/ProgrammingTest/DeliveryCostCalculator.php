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

    private static function countItemsByType(array $items, ItemType $itemType): int
    {
        return array_reduce(
            $items,
            static function(?int $carry, Item $item) use ($itemType) {
                return ($carry ?? 0) + ($item->type === $itemType ? 1 : 0);
            }
        );
    }

    private static function getComboSizeByDiscountType(ItemType $itemType): int
    {
        if ($itemType === ItemType::SmallParcelManiaDiscount) {
            return 4;
        }
        if ($itemType === ItemType::MediumParcelManiaDiscount) {
            return 3;
        }
        if ($itemType === ItemType::MixedParcelManiaDiscount) {
            return 5;
        }
        throw new \Exception('Item type is not a discount!');
    }


    private static function getCandidateDiscountCombinations(array $items): array
    {
        $candidates = [];

        $countSmall = self::countItemsByType($items, ItemType::Small);
        $countMedium = self::countItemsByType($items, ItemType::Medium);
        $countAny = count($items);

        $maxNumberOfSmallDiscounts = floor($countSmall / self::getComboSizeByDiscountType(ItemType::SmallParcelManiaDiscount));
        $maxNumberOfMediumDiscounts = floor($countMedium / self::getComboSizeByDiscountType(ItemType::MediumParcelManiaDiscount));
        $maxNumberOfMixedDiscounts = floor($countAny / self::getComboSizeByDiscountType(ItemType::MixedParcelManiaDiscount));

        for ($iSmallDiscounts = $maxNumberOfSmallDiscounts; $iSmallDiscounts >= 0; $iSmallDiscounts--) {
            for ($iMediumDiscounts = $maxNumberOfMediumDiscounts; $iMediumDiscounts >= 0; $iMediumDiscounts--) {
                for ($iMixedDiscounts = max(0, $maxNumberOfMixedDiscounts - $iMediumDiscounts - $iSmallDiscounts); $iMixedDiscounts >= 0; $iMixedDiscounts--) {
                    if ($iSmallDiscounts == 0 && $iMediumDiscounts == 0 && $iMixedDiscounts == 0) {
                        continue;
                    }

                    $candidate = [];
                    for ($i = 0; $i < $iSmallDiscounts; $i++) {
                        $candidate[] = ItemType::SmallParcelManiaDiscount;
                    }
                    for ($i = 0; $i < $iMediumDiscounts; $i++) {
                        $candidate[] = ItemType::MediumParcelManiaDiscount;
                    }
                    for ($i = 0; $i < $iMixedDiscounts; $i++) {
                        $candidate[] = ItemType::MixedParcelManiaDiscount;
                    }
                    $candidates[] = $candidate;
                }
            }
        }

        return $candidates;
    }

    // Thank you https://stackoverflow.com/a/27160465/718475
    private static function permutations(array $elements): \Generator
    {
        if (count($elements) <= 1) {
            yield $elements;
        } else {
            foreach (self::permutations(array_slice($elements, 1)) as $permutation) {
                foreach (range(0, count($elements) - 1) as $i) {
                    yield array_merge(
                        array_slice($permutation, 0, $i),
                        [$elements[0]],
                        array_slice($permutation, $i)
                    );
                }
            }
        }
    }

    private static function itemMatchesDiscount(Item $item, ItemType $discount): bool
    {
        if ($discount === ItemType::MixedParcelManiaDiscount) {
            return true;
        }
        if ($discount === ItemType::SmallParcelManiaDiscount && $item->type === ItemType::Small) {
            return true;
        }
        if ($discount === ItemType::MediumParcelManiaDiscount && $item->type === ItemType::Medium) {
            return true;
        }
        return false;
    }

    /**
     * @param array $candidateDiscountCombination - Note: This doesn't properly handle being called with discount
     *                                              combinations that wouldn't match any ordering of the items.
     * @param array $permutation
     * @return array
     * @throws \Exception
     */
    private static function getDiscountItemsForGivenDiscountComboAgainstExactSequenceOfItems(
        array $candidateDiscountCombination, array $permutation): array
    {
        $savings = 0;
        $discountItems = [];

        /** @var ItemType $currentDiscount */
        $currentDiscount = reset($candidateDiscountCombination);
        $cheapestItemInCurrentDiscount = null;
        $remainingSpace = self::getComboSizeByDiscountType($currentDiscount);
        foreach ($permutation as $item) {
            /** @var Item $item */
            if (self::itemMatchesDiscount($item, $currentDiscount)) {
                // It matches, so count it and set up the next thing to look for
                $remainingSpace--;
                if ($cheapestItemInCurrentDiscount === null || $item->cost < $cheapestItemInCurrentDiscount) {
                    $cheapestItemInCurrentDiscount = $item->cost;
                }
                if ($remainingSpace === 0) {
                    // We have filled up the discount!
                    $savings += $cheapestItemInCurrentDiscount;
                    $discountItems[] = new Item(null, null, null, null, $currentDiscount, -$cheapestItemInCurrentDiscount);

                    $currentDiscount = next($candidateDiscountCombination);
                    if ($currentDiscount === false) {
                        // There were no more discounts in the combination.  This is
                        // OK; there will just be some full-price items in the order.
                        return [$savings, $discountItems];
                    }
                    // We found another discount to collect items for
                    $remainingSpace = self::getComboSizeByDiscountType($currentDiscount);
                    $cheapestItemInCurrentDiscount = null;
                }
            } else {
                // The item type doesn't match, so this discount combo is invalid
                return [$savings, $discountItems];
            }
        }

        return [$savings, $discountItems];
    }

    /**
     * @param Item[] $items
     * @return Item[]
     */
    private static function calculateMultiParcelDiscounts(array $items): array
    {
        $winningCandidateTotalDiscount = 0;
        $winningCandidateDiscountItems = [];

        $candidateDiscountCombinations = self::getCandidateDiscountCombinations($items);

        foreach (self::permutations($items) as $permutation) {
            foreach ($candidateDiscountCombinations as $candidateDiscountCombination) {
                [$savings, $discountItems] = self::getDiscountItemsForGivenDiscountComboAgainstExactSequenceOfItems(
                    $candidateDiscountCombination,
                    $permutation
                );

                // Figure out if this is a winning discount combination
                if ($savings > $winningCandidateTotalDiscount) {
                    $winningCandidateTotalDiscount = $savings;
                    $winningCandidateDiscountItems = $discountItems;
                }
            }
        }

        return $winningCandidateDiscountItems;
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
        foreach (self::calculateMultiParcelDiscounts($items) as $discount) {
            $items[] = $discount;
            $totalCost += $discount->cost;
        }
        if ($shippingType === ShippingType::Speedy) {
            $items[] = new Item(null, null, null, null, ItemType::SpeedyShipping, $totalCost);
            $totalCost += $totalCost;
        }

        return ['items' => $items, 'totalCost' => $totalCost];
    }
}
