<?php

namespace ProgrammingTest;
class Item
{
    public ?float $width;
    public ?float $length;
    public ?float $height;
    public ?float $weight;
    public ?int $cost;
    public ?ItemType $type;

    public function __construct(?float $width, ?float $length, ?float $height, ?float $weight, ?ItemType $type = null, ?int $cost = null)
    {
        $this->width = $width;
        $this->length = $length;
        $this->height = $height;
        $this->weight = $weight;
        $this->type = $type;
        $this->cost = $cost;
    }
}