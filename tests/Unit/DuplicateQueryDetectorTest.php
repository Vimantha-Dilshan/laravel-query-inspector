<?php

declare(strict_types=1);

use Inspector\QueryMonitor\Detectors\DuplicateQueryDetector;
use Inspector\QueryMonitor\Support\QueryData;

function makeQueryData(string $sql, array $bindings = []): QueryData
{
    return new QueryData(
        sql: $sql,
        bindings: $bindings,
        executionTime: 5.0,
        connection: 'testing',
        queryType: 'SELECT',
        route: 'test.route',
        controller: null,
        requestUrl: 'http://localhost',
        httpMethod: 'GET',
        userId: null,
        environment: 'testing',
        requestId: 'req_unit_test',
    );
}

it('returns no duplicates when all queries are unique', function (): void {
    $detector = new DuplicateQueryDetector();

    $detector->track(makeQueryData('SELECT * FROM users WHERE id = ?', [1]));
    $detector->track(makeQueryData('SELECT * FROM users WHERE id = ?', [2]));
    $detector->track(makeQueryData('SELECT * FROM orders WHERE id = ?', [1]));

    expect($detector->getDuplicates())->toBeEmpty();
    expect($detector->hasDuplicates())->toBeFalse();
});

it('detects a query executed more than once', function (): void {
    $detector = new DuplicateQueryDetector();

    $query = makeQueryData('SELECT * FROM users WHERE id = ?', [1]);

    $detector->track($query);
    $detector->track($query);

    $duplicates = $detector->getDuplicates();

    expect($duplicates)->not->toBeEmpty();
    expect($detector->hasDuplicates())->toBeTrue();
});

it('counts how many times a duplicate was executed', function (): void {
    $detector = new DuplicateQueryDetector();

    $query = makeQueryData('SELECT * FROM products', []);

    $detector->track($query);
    $detector->track($query);
    $detector->track($query);

    $duplicates = $detector->getDuplicates();
    $first = array_values($duplicates)[0];

    expect($first['count'])->toBe(3);
});

it('treats queries with the same sql but different bindings as distinct', function (): void {
    $detector = new DuplicateQueryDetector();

    $detector->track(makeQueryData('SELECT * FROM users WHERE id = ?', [1]));
    $detector->track(makeQueryData('SELECT * FROM users WHERE id = ?', [2]));

    expect($detector->getDuplicates())->toBeEmpty();
});

it('resets its state correctly', function (): void {
    $detector = new DuplicateQueryDetector();
    $query = makeQueryData('SELECT 1');
    $detector->track($query);
    $detector->track($query);

    $detector->reset();

    expect($detector->getDuplicates())->toBeEmpty();
    expect($detector->hasDuplicates())->toBeFalse();
});
