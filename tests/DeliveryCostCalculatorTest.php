<?php

require "../vendor/autoload.php";

use PHPUnit\Framework\TestCase;
use ProgrammingTest\DeliveryCostCalculator;
use ProgrammingTest\Item;

class DeliveryCostCalculatorTest extends TestCase
{
    public function testSmallParcel()
    {
        $item = new Item(5.0, 5.0, 5.0);
        $this->assertSame(3, DeliveryCostCalculator::calculateCost($item));
    }
}
