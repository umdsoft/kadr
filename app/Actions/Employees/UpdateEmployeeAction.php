<?php

declare(strict_types=1);

namespace App\Actions\Employees;

use App\DTOs\EmployeeDTO;
use App\Models\Employee;
use App\Repositories\Contracts\EmployeeRepositoryInterface;

class UpdateEmployeeAction
{
    public function __construct(
        private EmployeeRepositoryInterface $repository,
    ) {}

    public function execute(int $id, EmployeeDTO $dto): Employee
    {
        return $this->repository->update($id, $dto);
    }
}
