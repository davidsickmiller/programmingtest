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
        $items = [new Item(5.0, 5.0, 5.0)];
        $returnVal = DeliveryCostCalculator::calculateCost($items);
        $this->assertSame(3, $returnVal['totalCost']);
        $this->assertCount(1, $returnVal['items']);
        $this->assertSame(3, $returnVal['items'][0]->cost);
        $this->assertSame(ItemType::Small, $returnVal['items'][0]->type);
    }

    public function testMediumParcel()
    {
        $items = [new Item(15.0, 15.0, 15.0)];
        $returnVal = DeliveryCostCalculator::calculateCost($items);
        $this->assertSame(8, $returnVal['totalCost']);
        $this->assertCount(1, $returnVal['items']);
        $this->assertSame(8, $returnVal['items'][0]->cost);
        $this->assertSame(ItemType::Medium, $returnVal['items'][0]->type);
    }

    public function testLargeParcel()
    {
        $items = [new Item(50.0, 50.0, 50.0)];
        $returnVal = DeliveryCostCalculator::calculateCost($items);
        $this->assertSame(15, $returnVal['totalCost']);
        $this->assertCount(1, $returnVal['items']);
        $this->assertSame(15, $returnVal['items'][0]->cost);
        $this->assertSame(ItemType::Large, $returnVal['items'][0]->type);
    }

    public function testXLargeParcel()
    {
        $items = [new Item(105.0, 5.0, 5.0)];
        $returnVal = DeliveryCostCalculator::calculateCost($items);
        $this->assertSame(25, $returnVal['totalCost']);
        $this->assertCount(1, $returnVal['items']);
        $this->assertSame(25, $returnVal['items'][0]->cost);
        $this->assertSame(ItemType::XLarge, $returnVal['items'][0]->type);
    }

    public function testTwoParcels()
    {
        $items = [
            new Item(15.0, 15.0, 15.0),
            new Item(105.0, 5.0, 5.0)
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
            new Item(15.0, 15.0, 15.0),
            new Item(105.0, 5.0, 5.0)
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
