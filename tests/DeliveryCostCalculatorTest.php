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
        $items = [new Item(5.0, 5.0, 5.0)];
        $returnVal = DeliveryCostCalculator::calculateCost($items);
        $this->assertSame(3, $returnVal['totalCost']);
        $this->assertCount(1, $returnVal['items']);
        $this->assertSame(3, $returnVal['items'][0]->cost);
        $this->assertSame(ItemType::Small, $returnVal['items'][0]->type);
    }
}
