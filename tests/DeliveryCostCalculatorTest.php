<?php

require "../vendor/autoload.php";

use PHPUnit\Framework\TestCase;
use ProgrammingTest\DeliveryCostCalculator;
use ProgrammingTest\Item;
use ProgrammingTest\ItemType;
use ProgrammingTest\ShippingType;

class DeliveryCostCalculatorTest extends TestCase
{
    public function testSmallParcel()
    {
        $items = [new Item(5.0, 5.0, 5.0, 0.1)];
        $returnVal = DeliveryCostCalculator::calculateCost($items);
        $this->assertSame(3, $returnVal['totalCost']);
        $this->assertCount(1, $returnVal['items']);
        $this->assertSame(3, $returnVal['items'][0]->cost);
        $this->assertSame(ItemType::Small, $returnVal['items'][0]->type);
    }

    public function testMediumParcel()
    {
        $items = [new Item(15.0, 15.0, 15.0, 0.1)];
        $returnVal = DeliveryCostCalculator::calculateCost($items);
        $this->assertSame(8, $returnVal['totalCost']);
        $this->assertCount(1, $returnVal['items']);
        $this->assertSame(8, $returnVal['items'][0]->cost);
        $this->assertSame(ItemType::Medium, $returnVal['items'][0]->type);
    }

    public function testLargeParcel()
    {
        $items = [new Item(50.0, 50.0, 50.0, 0.1)];
        $returnVal = DeliveryCostCalculator::calculateCost($items);
        $this->assertSame(15, $returnVal['totalCost']);
        $this->assertCount(1, $returnVal['items']);
        $this->assertSame(15, $returnVal['items'][0]->cost);
        $this->assertSame(ItemType::Large, $returnVal['items'][0]->type);
    }

    public function testXLargeParcel()
    {
        $items = [new Item(105.0, 5.0, 5.0, 0.1)];
        $returnVal = DeliveryCostCalculator::calculateCost($items);
        $this->assertSame(25, $returnVal['totalCost']);
        $this->assertCount(1, $returnVal['items']);
        $this->assertSame(25, $returnVal['items'][0]->cost);
        $this->assertSame(ItemType::XLarge, $returnVal['items'][0]->type);
    }

    public function testOverweightSmallParcel()
    {
        $items = [new Item(5.0, 5.0, 5.0, 3.5)];
        $returnVal = DeliveryCostCalculator::calculateCost($items);
        $expectedCost = 9;  // $3 plus $2 X 3KG overage (2.5 rounded up)
        $this->assertSame($expectedCost, $returnVal['totalCost']);
        $this->assertCount(1, $returnVal['items']);
        $this->assertSame($expectedCost, $returnVal['items'][0]->cost);
        $this->assertSame(ItemType::Small, $returnVal['items'][0]->type);
    }

    public function testOverweightMediumParcel()
    {
        $items = [new Item(15.0, 15.0, 15.0, 3.5)];
        $returnVal = DeliveryCostCalculator::calculateCost($items);
        $expectedCost = 10;  // $8 plus $2 X 1KG overage (0.5 rounded up)
        $this->assertSame($expectedCost, $returnVal['totalCost']);
        $this->assertCount(1, $returnVal['items']);
        $this->assertSame($expectedCost, $returnVal['items'][0]->cost);
        $this->assertSame(ItemType::Medium, $returnVal['items'][0]->type);
    }

    public function testOverweightLargeParcel()
    {
        $items = [new Item(50.0, 50.0, 50.0, 10.0)];
        $returnVal = DeliveryCostCalculator::calculateCost($items);
        $expectedCost = 23;  // $15 plus $2 X 4KG overage
        $this->assertSame($expectedCost, $returnVal['totalCost']);
        $this->assertCount(1, $returnVal['items']);
        $this->assertSame($expectedCost, $returnVal['items'][0]->cost);
        $this->assertSame(ItemType::Large, $returnVal['items'][0]->type);
    }

    public function testOverweightXLargeParcel()
    {
        $items = [new Item(105.0, 5.0, 5.0, 12.5)];
        $returnVal = DeliveryCostCalculator::calculateCost($items);
        $expectedCost = 31;  // $25 plus $2 X 3KG overage (2.5 rounded up)
        $this->assertSame($expectedCost, $returnVal['totalCost']);
        $this->assertCount(1, $returnVal['items']);
        $this->assertSame($expectedCost, $returnVal['items'][0]->cost);
        $this->assertSame(ItemType::XLarge, $returnVal['items'][0]->type);
    }

    public function testTwoParcels()
    {
        $items = [
            new Item(15.0, 15.0, 15.0, 0.1),
            new Item(105.0, 5.0, 5.0, 0.1)
        ];
        $returnVal = DeliveryCostCalculator::calculateCost($items);
        $this->assertSame(33, $returnVal['totalCost']);
        $this->assertCount(2, $returnVal['items']);

        $this->assertSame(8, $returnVal['items'][0]->cost);
        $this->assertSame(ItemType::Medium, $returnVal['items'][0]->type);

        $this->assertSame(25, $returnVal['items'][1]->cost);
        $this->assertSame(ItemType::XLarge, $returnVal['items'][1]->type);
    }

    public function testSpeedy()
    {
        $items = [
            new Item(15.0, 15.0, 15.0, 0.1),
            new Item(105.0, 5.0, 5.0, 0.1)
        ];
        $returnVal = DeliveryCostCalculator::calculateCost($items, ShippingType::Speedy);
        $this->assertSame(66, $returnVal['totalCost']);

        $this->assertCount(3, $returnVal['items']);

        $this->assertSame(8, $returnVal['items'][0]->cost);
        $this->assertSame(ItemType::Medium, $returnVal['items'][0]->type);

        $this->assertSame(25, $returnVal['items'][1]->cost);
        $this->assertSame(ItemType::XLarge, $returnVal['items'][1]->type);

        $this->assertSame(33, $returnVal['items'][2]->cost);
        $this->assertSame(ItemType::SpeedyShipping, $returnVal['items'][2]->type);
    }
}
