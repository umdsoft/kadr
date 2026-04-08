<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\DTOs\EmployeeDTO;
use App\Models\Employee;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface EmployeeRepositoryInterface
{
    public function find(int $id): ?Employee;

    public function findByUuid(string $uuid): ?Employee;

    public function create(EmployeeDTO $dto): Employee;

    public function update(int $id, EmployeeDTO $dto): Employee;

    public function delete(int $id): bool;

    public function restore(int $id): bool;

    public function forceDelete(int $id): bool;

    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginate(array $filters = [], int $perPage = 25): LengthAwarePaginator;
}
