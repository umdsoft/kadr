<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Exports\MalumotnomaDocxExporter;
use App\Repositories\Contracts\EmployeeRepositoryInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportController extends Controller
{
    public function __construct(
        private EmployeeRepositoryInterface $repository,
        private MalumotnomaDocxExporter $exporter,
    ) {}

    /**
     * Битта ходимнинг Маълумотномасини .docx сифатида юклаб олиш.
     */
    public function downloadMalumotnoma(int $id): BinaryFileResponse
    {
        $employee = $this->repository->find($id);
        abort_if($employee === null, 404);

        $path = $this->exporter->export($employee);
        $filename = $this->exporter->generateFilename($employee);

        return response()->download($path, $filename)->deleteFileAfterSend();
    }
}
