<?php

declare(strict_types=1);

namespace Inspector\QueryMonitor\Detectors;

class SlowQueryDetector
{
    public function __construct(
        private readonly float $thresholdMs
    ) {}

    /**
     * Determine whether the given execution time qualifies as slow.
     */
    public function detect(float $executionTimeMs): bool
    {
        return $executionTimeMs >= $this->thresholdMs;
    }

    public function getThreshold(): float
    {
        return $this->thresholdMs;
    }
}
