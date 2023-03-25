<?php

require "./DeliveryCostCalculator.php";

use PHPUnit\Framework\TestCase;

class DeliveryCostCalculatorTest extends TestCase
{
    public function testSmallParcel()
    {
        $this->assertSame(3, DeliveryCostCalculator::calculateCost(5.0, 5.0, 5.0));
    }
}
