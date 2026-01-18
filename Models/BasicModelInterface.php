<?php

declare(strict_types=1);

namespace Models;

interface BasicModelInterface
{
    public function __construct(?string $table);
    public function getColumns(string $table): array;
    public static function applySortingAndLimiting(string $query, ?string $orderBy = null, ?string $sort = null, ?int $limit = null): string;
    public function setter(string $table, string $mainColumn): void;
    public function exists(string|int $param): bool;
    public function getAll(?array $where = null, ?string $orderBy = null, ?string $sort = null, ?int $limit = null): array;
    public function get(string|int|null $param = null, ?string $sort = null, ?int $limit = null, ?string $orderBy = null): array;
    public function create(array $data): int;
    public function update(array $data, int $id): int;
    public function delete(int $id): bool;
}
