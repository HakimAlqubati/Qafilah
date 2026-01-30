<?php

declare(strict_types=1);

namespace App\DTOs\Reports;

use Carbon\CarbonImmutable;
use DateTimeInterface;

/**
 * Data Transfer Object for Sales Report Filters
 * 
 * Immutable value object that encapsulates all filtering criteria
 * for generating sales reports.
 */
readonly class SalesFilterDTO
{
    public ?CarbonImmutable $startDate;
    public ?CarbonImmutable $endDate;

    public function __construct(
        DateTimeInterface|string|null $startDate = null,
        DateTimeInterface|string|null $endDate = null,
        public ?int $vendorId = null,
        public ?string $status = null,
        public ?string $paymentStatus = null,
        public ?int $customerId = null,
        public ?int $categoryId = null,
    ) {
        // Dates are null by default (no filtering)
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
        return new self(
            startDate: $data['start_date'] ?? $data['startDate'] ?? null,
            endDate: $data['end_date'] ?? $data['endDate'] ?? null,
            vendorId: isset($data['vendor_id']) ? (int) $data['vendor_id'] : null,
            status: $data['status'] ?? null,
            paymentStatus: $data['payment_status'] ?? $data['paymentStatus'] ?? null,
            customerId: isset($data['customer_id']) ? (int) $data['customer_id'] : null,
            categoryId: isset($data['category_id']) ? (int) $data['category_id'] : null,
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
            'status' => $this->status,
            'payment_status' => $this->paymentStatus,
            'customer_id' => $this->customerId,
            'category_id' => $this->categoryId,
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
            || $this->status !== null
            || $this->paymentStatus !== null
            || $this->customerId !== null
            || $this->categoryId !== null;
    }

    /**
     * Check if date range filter is applied
     */
    public function hasDateFilter(): bool
    {
        return $this->startDate !== null || $this->endDate !== null;
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
}
