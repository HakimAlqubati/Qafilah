<?php

declare(strict_types=1);

namespace App\DTOs\Reports\Vendors;

use Carbon\CarbonImmutable;
use DateTimeInterface;

/**
 * Data Transfer Object for Vendors Report Filters
 * 
 * Immutable value object that encapsulates all filtering criteria
 * for generating vendor performance reports.
 */
readonly class VendorsFilterDTO
{
    public ?CarbonImmutable $startDate;
    public ?CarbonImmutable $endDate;

    public function __construct(
        DateTimeInterface|string|null $startDate = null,
        DateTimeInterface|string|null $endDate = null,
        public ?int $vendorId = null,
        public ?int $categoryId = null,
        public ?string $orderStatus = null,
        public ?string $sortBy = 'revenue', // revenue, orders, products
        public string $sortOrder = 'desc',
        public int $limit = 10,
    ) {
        $this->startDate = $this->parseDate($startDate);
        $this->endDate = $this->parseDate($endDate);
    }

    /**
     * Parse date input to CarbonImmutable instance
     */
    private function parseDate(DateTimeInterface|string|null $date): ?CarbonImmutable
    {
        if ($date === null) {
            return null;
        }

        if ($date instanceof CarbonImmutable) {
            return $date;
        }

        if ($date instanceof DateTimeInterface) {
            return CarbonImmutable::instance($date);
        }

        return CarbonImmutable::parse($date);
    }

    /**
     * Create DTO from array (useful for form requests)
     * 
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $sortBy = $data['sort_by'] ?? 'revenue';
        if (!in_array($sortBy, ['revenue', 'orders', 'products'], true)) {
            $sortBy = 'revenue';
        }

        $sortOrder = strtolower($data['sort_order'] ?? 'desc');
        if (!in_array($sortOrder, ['asc', 'desc'], true)) {
            $sortOrder = 'desc';
        }

        return new self(
            startDate: $data['start_date'] ?? null,
            endDate: $data['end_date'] ?? null,
            vendorId: isset($data['vendor_id']) ? (int) $data['vendor_id'] : null,
            categoryId: isset($data['category_id']) ? (int) $data['category_id'] : null,
            orderStatus: $data['order_status'] ?? null,
            sortBy: $sortBy,
            sortOrder: $sortOrder,
            limit: isset($data['limit']) ? min((int) $data['limit'], 100) : 10,
        );
    }

    /**
     * Convert DTO to array representation
     * 
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'start_date' => $this->startDate?->toDateTimeString(),
            'end_date' => $this->endDate?->toDateTimeString(),
            'vendor_id' => $this->vendorId,
            'category_id' => $this->categoryId,
            'order_status' => $this->orderStatus,
            'sort_by' => $this->sortBy,
            'sort_order' => $this->sortOrder,
            'limit' => $this->limit,
        ];
    }

    /**
     * Check if any filters are applied
     */
    public function hasFilters(): bool
    {
        return $this->startDate !== null
            || $this->endDate !== null
            || $this->vendorId !== null
            || $this->categoryId !== null
            || $this->orderStatus !== null;
    }

    /**
     * Get the date range as formatted string
     */
    public function getDateRangeLabel(): ?string
    {
        if ($this->startDate === null && $this->endDate === null) {
            return null;
        }

        $start = $this->startDate?->format('Y-m-d') ?? 'البداية';
        $end = $this->endDate?->format('Y-m-d') ?? 'الآن';

        return sprintf('%s - %s', $start, $end);
    }

    /**
     * Get the SQL order by column
     */
    public function getOrderByColumn(): string
    {
        return match ($this->sortBy) {
            'orders' => 'orders_count',
            'products' => 'products_count',
            default => 'total_revenue',
        };
    }
}
