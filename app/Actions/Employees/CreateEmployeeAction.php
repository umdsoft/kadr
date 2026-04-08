<?php

declare(strict_types=1);

namespace App\Actions\Employees;

use App\DTOs\EmployeeDTO;
use App\Models\Employee;
use App\Repositories\Contracts\EmployeeRepositoryInterface;

class CreateEmployeeAction
{
    public function __construct(
        private EmployeeRepositoryInterface $repository,
    ) {}

    public function execute(EmployeeDTO $dto): Employee
    {
        return $this->repository->create($dto);
    }
}
