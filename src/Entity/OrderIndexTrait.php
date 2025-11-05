<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Override;

trait OrderIndexTrait
{
    #[ORM\Column(type: Types::INTEGER)]
    protected int $orderIndex = OrderIndexInterface::INIT_ORDER_INDEX;

    #[Override]
    public function getOrderIndex(): int
    {
        return $this->orderIndex;
    }

    #[Override]
    public function updateOrderIndexIfNotSet(int $orderIndex): void
    {
        if ($this->orderIndex !== OrderIndexInterface::INIT_ORDER_INDEX) {
            return;
        }

        $this->orderIndex = $orderIndex;
    }

    /**
     * @todo fixnut setovanie
     */
    #[Override]
    public function setOrderIndex(?int $orderIndex): void
    {
        if ($orderIndex === null) {
            $orderIndex = OrderIndexInterface::INIT_ORDER_INDEX;
        }
        $this->orderIndex = $orderIndex;
    }
}
