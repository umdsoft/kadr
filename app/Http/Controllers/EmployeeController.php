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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class EmployeeController extends Controller
{
    public function __construct(
        private EmployeeRepositoryInterface $repository,
    ) {
        // Авторизация: index→viewAny, create/store→create, show→view,
        // edit/update→update, destroy→delete (EmployeePolicy + sameTenant).
        $this->authorizeResource(Employee::class, 'employee');
    }

    public function index(Request $request): Response
    {
        $filterKeys = ['search', 'department_id', 'education_level', 'nationality', 'birth_district_id', 'specialty'];

        $employees = $this->repository->paginate(
            filters: $request->only($filterKeys),
            perPage: (int) $request->input('per_page', 25),
        );

        return Inertia::render('Employees/Index', [
            'employees' => $employees,
            'filters' => $request->only($filterKeys),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Employees/Create');
    }

    public function store(
        StoreEmployeeRequest $request,
        CreateEmployeeAction $action,
        SaveWorkHistoryAction $saveWorkHistory,
        SaveRelativesAction $saveRelatives,
    ): RedirectResponse {
        $validated = $request->validated();

        // Расм юклаш — МАХФИЙ (private/local) дискда, авторизацияланган маршрут орқали берилади.
        if ($request->hasFile('photo')) {
            $validated['photo_path'] = $request->file('photo')->store('employee-photos', 'local');
        }

        $dto = EmployeeDTO::fromArray($validated);

        // Ходим + меҳнат фаолияти + қариндошлар — битта транзаксияда:
        // ўрта йўлда хато бўлса ярим яратилган ходим қолмаслиги учун.
        // Валидацияланган payload (input() эмас) — StoreEmployeeRequest work_history/relatives'ни ҳам текширади.
        $workHistory = $validated['work_history'] ?? [];
        $relatives = $validated['relatives'] ?? [];

        $employee = DB::transaction(function () use ($dto, $workHistory, $relatives, $action, $saveWorkHistory, $saveRelatives): Employee {
            $employee = $action->execute($dto);

            if (is_array($workHistory) && count($workHistory) > 0) {
                $saveWorkHistory->execute($employee, $workHistory);
            }

            if (is_array($relatives) && count($relatives) > 0) {
                $saveRelatives->execute($employee, $relatives);
            }

            return $employee;
        });

        return redirect()
            ->route('employees.show', $employee->id)
            ->with('success', 'Ходим муваффақиятли яратилди.');
    }

    public function show(Employee $employee): Response
    {
        $employee->load(['workHistory', 'relatives', 'department', 'position', 'birthRegion', 'birthDistrict']);

        return Inertia::render('Employees/Show', [
            'employee' => $employee,
        ]);
    }

    public function edit(Employee $employee): Response
    {
        $employee->load(['department', 'position', 'birthRegion', 'birthDistrict', 'workHistory', 'relatives']);

        // Таҳрирлаш формаси учун махфий майдонларни очиб берамиз (битта авторизацияланган ёзув).
        $employee->makeVisible(['jshshir', 'passport_series', 'passport_number']);

        return Inertia::render('Employees/Edit', [
            'employee' => $employee,
        ]);
    }

    public function update(UpdateEmployeeRequest $request, Employee $employee, UpdateEmployeeAction $action): RedirectResponse
    {
        $validated = $request->validated();
        $oldPhoto = $employee->photo_path;

        // Расм юклаш — МАХФИЙ (private/local) дискда. Янги расм бўлмаса, мавжуди сақланади
        // (мижоз photo_path ни ўзгартира олмайди — path traversal олдини олиш).
        $validated['photo_path'] = $request->hasFile('photo')
            ? $request->file('photo')->store('employee-photos', 'local')
            : $employee->photo_path;

        $dto = EmployeeDTO::fromArray($validated);
        $action->execute($employee->id, $dto);

        // Эски расмни ўчириш (янгиси юкланган бўлса) — orphan файллар қолмаслиги учун (M9).
        if ($request->hasFile('photo') && $oldPhoto && $oldPhoto !== $validated['photo_path']) {
            Storage::disk('local')->delete($oldPhoto);
        }

        return redirect()
            ->route('employees.show', $employee->id)
            ->with('success', 'Ходим маълумотлари янгиланди.');
    }

    public function destroy(Employee $employee, DeleteEmployeeAction $action): RedirectResponse
    {
        $action->execute($employee->id);

        return redirect()
            ->route('employees.index')
            ->with('success', 'Ходим архивга ўтказилди.');
    }

    /**
     * Ходим расмини авторизация билан бериш (public диск ўрнига).
     * Route model binding tenant scope'ни қўллайди; қўшимча view рухсати текширилади.
     */
    public function photo(Employee $employee): BinaryFileResponse
    {
        $this->authorize('view', $employee);

        abort_if($employee->photo_path === null || $employee->photo_path === '', 404);
        abort_unless(Storage::disk('local')->exists($employee->photo_path), 404);

        return response()->file(Storage::disk('local')->path($employee->photo_path));
    }

    /**
     * 3-блок: Меҳнат фаолиятини сақлаш.
     */
    public function saveWorkHistory(
        SaveWorkHistoryRequest $request,
        Employee $employee,
        SaveWorkHistoryAction $action,
    ): RedirectResponse {
        $this->authorize('update', $employee);

        $action->execute($employee, $request->validated('work_history'));

        return redirect()
            ->route('employees.show', $employee->id)
            ->with('success', 'Меҳнат фаолияти сақланди.');
    }

    /**
     * 4-блок: Яқин қариндошларни сақлаш.
     */
    public function saveRelatives(
        SaveRelativesRequest $request,
        Employee $employee,
        SaveRelativesAction $action,
    ): RedirectResponse {
        $this->authorize('update', $employee);

        $action->execute($employee, $request->validated('relatives'));

        return redirect()
            ->route('employees.show', $employee->id)
            ->with('success', 'Қариндошлар маълумотлари сақланди.');
    }
}
