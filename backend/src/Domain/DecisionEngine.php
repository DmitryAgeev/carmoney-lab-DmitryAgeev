<?php

declare(strict_types=1);

namespace CarMoneyLab\Domain;

/**
 * Решение по заявке на основании LTV и пробега.
 *
 *   LTV <= approve_max              -> approve
 *   approve_max < LTV <= review_max -> review
 *   LTV > review_max                -> reject
 */
final class DecisionEngine
{
    public const APPROVE = 'approve';
    public const REVIEW = 'review';
    public const REJECT = 'reject';

    private float $approveMax;
    private float $reviewMax;
    private ?int $reviewMileageThresholdKm;

    /** @param array{approve_max:float,review_max:float} $thresholds */
    public function __construct(array $thresholds, ?int $reviewMileageThresholdKm = null)
    {
        $this->approveMax = $thresholds['approve_max'];
        $this->reviewMax = $thresholds['review_max'];
        $this->reviewMileageThresholdKm = $reviewMileageThresholdKm;
    }

    public function decide(float $ltv, int $mileage): string
    {
        // The outcome above review_max remains governed by the existing LTV rule.
        if ($ltv > $this->reviewMax) {
            return self::REJECT;
        }

        if ($this->reviewMileageThresholdKm !== null && $mileage > $this->reviewMileageThresholdKm) {
            return self::REVIEW;
        }

        if ($ltv < $this->approveMax) {
            return self::APPROVE;
        }

        return self::REVIEW;
    }
}
