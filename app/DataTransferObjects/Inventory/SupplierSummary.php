<?php

namespace App\DataTransferObjects\Inventory;

final readonly class SupplierSummary
{
    public function __construct(
        public ?int $id,
        public string $name,
        public ?string $email,
        public ?string $phone,
    ) {}

    /** @return array{id: ?int, name: string, email: ?string, phone: ?string} */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
        ];
    }
}
