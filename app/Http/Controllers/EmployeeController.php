<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Employees\CreateEmployeeAction;
use App\Actions\Employees\DeleteEmployeeAction;
use App\Actions\Employees\UpdateEmployeeAction;
use App\Actions\Relatives\SaveRelativesAction;
use App\Actions\WorkHistory\SaveWorkHistoryAction;
use App\DTOs\EmployeeDTO;
use App\Http\Requests\SaveRelativesRequest;
use App\Http\Requests\SaveWorkHistoryRequest;
use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Models\Employee;
use App\Repositories\Contracts\EmployeeRepositoryInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EmployeeController extends Controller
{
    public function __construct(
        private EmployeeRepositoryInterface $repository,
    ) {}

    public function index(Request $request): Response
    {
        $employees = $this->repository->paginate(
            filters: $request->only(['search', 'department_id']),
            perPage: (int) $request->input('per_page', 25),
        );

        return Inertia::render('Employees/Index', [
            'employees' => $employees,
            'filters' => $request->only(['search', 'department_id']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Employees/Create');
    }

    public function store(StoreEmployeeRequest $request, CreateEmployeeAction $action): RedirectResponse
    {
        $dto = EmployeeDTO::fromArray($request->validated());
        $employee = $action->execute($dto);

        return redirect()
            ->route('employees.show', $employee->id)
            ->with('success', 'Ходим муваффақиятли яратилди.');
    }

    public function show(int $id): Response
    {
        $employee = $this->repository->find($id);

        abort_if($employee === null, 404);

        $employee->load(['workHistory', 'relatives']);

        return Inertia::render('Employees/Show', [
            'employee' => $employee,
        ]);
    }

    public function edit(int $id): Response
    {
        $employee = $this->repository->find($id);

        abort_if($employee === null, 404);

        return Inertia::render('Employees/Edit', [
            'employee' => $employee,
        ]);
    }

    public function update(UpdateEmployeeRequest $request, int $id, UpdateEmployeeAction $action): RedirectResponse
    {
        $dto = EmployeeDTO::fromArray($request->validated());
        $action->execute($id, $dto);

        return redirect()
            ->route('employees.show', $id)
            ->with('success', 'Ходим маълумотлари янгиланди.');
    }

    public function destroy(int $id, DeleteEmployeeAction $action): RedirectResponse
    {
        $action->execute($id);

        return redirect()
            ->route('employees.index')
            ->with('success', 'Ходим архивга ўтказилди.');
    }

    /**
     * 3-блок: Меҳнат фаолиятини сақлаш.
     */
    public function saveWorkHistory(
        SaveWorkHistoryRequest $request,
        int $id,
        SaveWorkHistoryAction $action,
    ): RedirectResponse {
        $employee = $this->repository->find($id);
        abort_if($employee === null, 404);

        $action->execute($employee, $request->validated('work_history'));

        return redirect()
            ->route('employees.show', $id)
            ->with('success', 'Меҳнат фаолияти сақланди.');
    }

    /**
     * 4-блок: Яқин қариндошларни сақлаш.
     */
    public function saveRelatives(
        SaveRelativesRequest $request,
        int $id,
        SaveRelativesAction $action,
    ): RedirectResponse {
        $employee = $this->repository->find($id);
        abort_if($employee === null, 404);

        $action->execute($employee, $request->validated('relatives'));

        return redirect()
            ->route('employees.show', $id)
            ->with('success', 'Қариндошлар маълумотлари сақланди.');
    }
}
