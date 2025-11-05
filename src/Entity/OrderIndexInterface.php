<?php

declare(strict_types=1);

namespace App\Entity;

interface OrderIndexInterface
{
    public const int INIT_ORDER_INDEX = 0;

    public function getOrderIndex(): int;

    public function updateOrderIndexIfNotSet(int $orderIndex): void;

    public function setOrderIndex(int $orderIndex): void;
}
