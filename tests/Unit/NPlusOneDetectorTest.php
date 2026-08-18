<?php

declare(strict_types=1);

use Inspector\QueryMonitor\Detectors\NPlusOneDetector;
use Inspector\QueryMonitor\Support\QueryData;

function makeNPlusOneQueryData(string $sql, array $bindings = []): QueryData
{
    return new QueryData(
        sql: $sql,
        bindings: $bindings,
        executionTime: 2.0,
        connection: 'testing',
        queryType: 'SELECT',
        route: 'users.index',
        controller: null,
        requestUrl: 'http://localhost/users',
        httpMethod: 'GET',
        userId: null,
        environment: 'testing',
        requestId: 'req_nplusone',
    );
}

it('detects a potential N+1 when the same pattern appears >= threshold times', function (): void {
    $detector = new NPlusOneDetector(threshold: 3);

    $sql = 'select * from `orders` where `customer_id` = ?';

    for ($i = 1; $i <= 5; $i++) {
        $detector->track(makeNPlusOneQueryData($sql, [$i]));
    }

    expect($detector->getPotentialIssues())->not->toBeEmpty();
    expect($detector->hasIssues())->toBeTrue();
});

it('does not flag patterns that appear fewer times than the threshold', function (): void {
    $detector = new NPlusOneDetector(threshold: 10);

    $sql = 'select * from `orders` where `customer_id` = ?';

    for ($i = 1; $i <= 3; $i++) {
        $detector->track(makeNPlusOneQueryData($sql, [$i]));
    }

    expect($detector->getPotentialIssues())->toBeEmpty();
    expect($detector->hasIssues())->toBeFalse();
});

it('normalises queries with different binding values to the same pattern', function (): void {
    $detector = new NPlusOneDetector(threshold: 3);

    // Different IDs, same structural pattern
    for ($id = 1; $id <= 5; $id++) {
        $detector->track(makeNPlusOneQueryData(
            "select * from `orders` where `customer_id` = {$id}"
        ));
    }

    // All should collapse to one pattern and trigger the threshold
    $issues = $detector->getPotentialIssues();
    expect($issues)->not->toBeEmpty();
});

it('reports the configured threshold', function (): void {
    $detector = new NPlusOneDetector(threshold: 15);

    expect($detector->getThreshold())->toBe(15);
});

it('resets state correctly', function (): void {
    $detector = new NPlusOneDetector(threshold: 2);

    $sql = 'select * from `users`';
    $detector->track(makeNPlusOneQueryData($sql));
    $detector->track(makeNPlusOneQueryData($sql));
    $detector->track(makeNPlusOneQueryData($sql));

    $detector->reset();

    expect($detector->getPotentialIssues())->toBeEmpty();
    expect($detector->hasIssues())->toBeFalse();
});
