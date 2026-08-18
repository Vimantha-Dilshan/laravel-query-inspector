<?php

declare(strict_types=1);

use Inspector\QueryMonitor\Detectors\SlowQueryDetector;

it('returns true when execution time equals the threshold', function (): void {
    $detector = new SlowQueryDetector(thresholdMs: 500.0);

    expect($detector->detect(500.0))->toBeTrue();
});

it('returns true when execution time exceeds the threshold', function (): void {
    $detector = new SlowQueryDetector(thresholdMs: 500.0);

    expect($detector->detect(501.0))->toBeTrue();
    expect($detector->detect(1200.0))->toBeTrue();
});

it('returns false when execution time is below the threshold', function (): void {
    $detector = new SlowQueryDetector(thresholdMs: 500.0);

    expect($detector->detect(499.9))->toBeFalse();
    expect($detector->detect(0.0))->toBeFalse();
});

it('reports the correct threshold value', function (): void {
    $detector = new SlowQueryDetector(thresholdMs: 250.0);

    expect($detector->getThreshold())->toBe(250.0);
});

it('handles fractional thresholds correctly', function (): void {
    $detector = new SlowQueryDetector(thresholdMs: 0.5);

    expect($detector->detect(0.4))->toBeFalse();
    expect($detector->detect(0.5))->toBeTrue();
    expect($detector->detect(0.6))->toBeTrue();
});
