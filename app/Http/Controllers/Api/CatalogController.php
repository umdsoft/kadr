<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\District;
use App\Models\Nationality;
use App\Models\Position;
use App\Models\Region;
use App\Models\Specialty;
use Illuminate\Http\JsonResponse;

/**
 * Каталог маълумотларини JSON форматда қайтаради.
 * Frontend dropdown lar учун ягона манба (DRY).
 */
class CatalogController extends Controller
{
    public function regions(): JsonResponse
    {
        return response()->json(
            Region::where('is_active', true)
                ->orderBy('sort_order')
                ->get(['id', 'name_cyr', 'name_lat', 'code']),
        );
    }

    public function districts(Region $region): JsonResponse
    {
        return response()->json(
            $region->districts()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get(['id', 'region_id', 'name_cyr', 'name_lat', 'code', 'is_city']),
        );
    }

    public function mahallas(District $district): JsonResponse
    {
        return response()->json(
            $district->mahallas()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get(['id', 'district_id', 'name_cyr', 'name_lat']),
        );
    }

    public function nationalities(): JsonResponse
    {
        return response()->json(
            Nationality::where('is_active', true)
                ->orderBy('sort_order')
                ->get(['id', 'name_cyr', 'name_lat']),
        );
    }

    public function specialties(): JsonResponse
    {
        return response()->json(
            Specialty::where('is_active', true)
                ->orderBy('sort_order')
                ->get(['id', 'name_cyr', 'name_lat']),
        );
    }

    public function departments(): JsonResponse
    {
        return response()->json(
            Department::with('children')
                ->where('is_active', true)
                ->whereNull('parent_id')
                ->orderBy('sort_order')
                ->get(['id', 'parent_id', 'name_cyr', 'name_lat', 'code', 'type']),
        );
    }

    public function positions(?Department $department = null): JsonResponse
    {
        $query = Position::where('is_active', true)->orderBy('sort_order');

        if ($department) {
            $query->where(function ($q) use ($department) {
                $q->where('department_id', $department->id)
                    ->orWhereNull('department_id');
            });
        }

        return response()->json(
            $query->get(['id', 'department_id', 'name_cyr', 'name_lat']),
        );
    }
}
