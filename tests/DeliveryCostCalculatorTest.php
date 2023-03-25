<?php

require "../vendor/autoload.php";

use PHPUnit\Framework\TestCase;
use ProgrammingTest\DeliveryCostCalculator;
use ProgrammingTest\Item;
use ProgrammingTest\ItemType;

class DeliveryCostCalculatorTest extends TestCase
{
    public function testSmallParcel()
    {
        $item = new Item(5.0, 5.0, 5.0);
        $returnVal = DeliveryCostCalculator::calculateCost($item);
        $this->assertSame(3, $returnVal->cost);
        $this->assertSame(ItemType::Small, $returnVal->type);
    }
}
