<?php

namespace App\DataTransferObjects\Tenants;

final readonly class TenantHealthData
{
    public function __construct(
        public string $tenantId,
        public string $name,
        public string $owner,
        public string $email,
        public string $plan,
        public int $healthScore,
        public int $loginScore,
        public int $orderScore,
        public int $productScore,
        public int $setupScore,
    ) {}

    public static function unavailable(
        string $tenantId,
        string $name,
        string $owner,
        string $email,
        string $plan,
    ): self {
        return new self($tenantId, $name, $owner, $email, $plan, 0, 0, 0, 0, 0);
    }

    /** @return array{id: string, name: string, owner: string, email: string, plan: string, health_score: int, login_score: int, order_score: int, product_score: int, setup_score: int} */
    public function toArray(): array
    {
        return [
            'id' => $this->tenantId,
            'name' => $this->name,
            'owner' => $this->owner,
            'email' => $this->email,
            'plan' => $this->plan,
            'health_score' => $this->healthScore,
            'login_score' => $this->loginScore,
            'order_score' => $this->orderScore,
            'product_score' => $this->productScore,
            'setup_score' => $this->setupScore,
        ];
    }
}
