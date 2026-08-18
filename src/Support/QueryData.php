<?php

declare(strict_types=1);

namespace Inspector\QueryMonitor\Support;

final class QueryData
{
    public function __construct(
        public readonly string $sql,
        public readonly array $bindings,
        public readonly float $executionTime,
        public readonly string $connection,
        public readonly string $queryType,
        public readonly ?string $route,
        public readonly ?string $controller,
        public readonly string $requestUrl,
        public readonly string $httpMethod,
        public readonly ?int $userId,
        public readonly string $environment,
        public readonly string $requestId,
        public readonly bool $isSlow = false,
        public readonly array $metadata = [],
    ) {}

    public function toArray(): array
    {
        return [
            'sql' => $this->sql,
            'bindings' => $this->bindings,
            'execution_time' => $this->executionTime,
            'connection' => $this->connection,
            'query_type' => $this->queryType,
            'route' => $this->route,
            'controller' => $this->controller,
            'request_url' => $this->requestUrl,
            'http_method' => $this->httpMethod,
            'user_id' => $this->userId,
            'environment' => $this->environment,
            'request_id' => $this->requestId,
            'is_slow' => $this->isSlow,
            'metadata' => $this->metadata,
        ];
    }

    public function isSelect(): bool
    {
        return $this->queryType === 'SELECT';
    }

    public function isWriteOperation(): bool
    {
        return in_array($this->queryType, ['INSERT', 'UPDATE', 'DELETE'], true);
    }
}
