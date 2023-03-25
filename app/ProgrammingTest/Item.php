<?php

namespace ProgrammingTest;
class Item
{
    public ?float $width;
    public ?float $length;
    public ?float $height;
    public ?int $cost;
    public ?ItemType $type;

    public function __construct(?float $width, ?float $length, ?float $height, ?ItemType $type = null, ?int $cost = null)
    {
        $this->width = $width;
        $this->length = $length;
        $this->height = $height;
        $this->type = $type;
        $this->cost = $cost;
    }
}