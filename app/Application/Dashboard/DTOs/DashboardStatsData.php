<?php

declare(strict_types=1);

namespace App\Application\Dashboard\DTOs;

/**
 * DTO para estadísticas generales del dashboard.
 */
readonly class DashboardStatsData
{
    public function __construct(
        public int $totalLeads,
        public int $totalDeals,
        public int $openDeals,
        public int $wonDeals,
        public int $lostDeals,
        public float $totalWonValue,
        public float $conversionRate,
        public int $leadsThisMonth,
        public int $dealsThisMonth,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            totalLeads: $data['total_leads'] ?? 0,
            totalDeals: $data['total_deals'] ?? 0,
            openDeals: $data['open_deals'] ?? 0,
            wonDeals: $data['won_deals'] ?? 0,
            lostDeals: $data['lost_deals'] ?? 0,
            totalWonValue: (float) ($data['total_won_value'] ?? 0),
            conversionRate: (float) ($data['conversion_rate'] ?? 0),
            leadsThisMonth: $data['leads_this_month'] ?? 0,
            dealsThisMonth: $data['deals_this_month'] ?? 0,
        );
    }

    public function toArray(): array
    {
        return [
            'total_leads' => $this->totalLeads,
            'total_deals' => $this->totalDeals,
            'open_deals' => $this->openDeals,
            'won_deals' => $this->wonDeals,
            'lost_deals' => $this->lostDeals,
            'total_won_value' => $this->totalWonValue,
            'conversion_rate' => $this->conversionRate,
            'leads_this_month' => $this->leadsThisMonth,
            'deals_this_month' => $this->dealsThisMonth,
        ];
    }
}
