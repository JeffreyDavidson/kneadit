<?php

namespace App\ValueObjects;

final readonly class TenantHealthScore
{
    public int $score;

    public int $loginScore;

    public int $orderScore;

    public int $productScore;

    public int $setupScore;

    public function __construct(
        ?int $daysSinceLogin,
        int $orderCount,
        int $productCount,
        int $setupCompleted,
        int $setupTotal = 7,
    ) {
        $this->loginScore = self::scoreLogin($daysSinceLogin);
        $this->orderScore = self::scoreOrders($orderCount);
        $this->productScore = self::scoreProducts($productCount);
        $this->setupScore = $setupTotal > 0
            ? (int) round(($setupCompleted / $setupTotal) * 30)
            : 0;
        $this->score = min($this->loginScore + $this->orderScore + $this->productScore + $this->setupScore, 100);
    }

    public function grade(): string
    {
        return match (true) {
            $this->score > 70 => 'healthy',
            $this->score >= 40 => 'at-risk',
            default => 'critical',
        };
    }

    public function filamentColor(): string
    {
        return match ($this->grade()) {
            'healthy' => 'success',
            'at-risk' => 'warning',
            'critical' => 'danger',
            default => 'gray',
        };
    }

    private static function scoreLogin(?int $days): int
    {
        if ($days === null) {
            return 0;
        }

        return match (true) {
            $days <= 1 => 25,
            $days <= 7 => 20,
            $days <= 14 => 10,
            $days <= 30 => 5,
            default => 0,
        };
    }

    private static function scoreOrders(int $count): int
    {
        return match (true) {
            $count >= 50 => 25,
            $count >= 20 => 20,
            $count >= 5 => 10,
            $count > 0 => 5,
            default => 0,
        };
    }

    private static function scoreProducts(int $count): int
    {
        return match (true) {
            $count >= 20 => 20,
            $count >= 10 => 15,
            $count >= 3 => 10,
            $count > 0 => 5,
            default => 0,
        };
    }
}
