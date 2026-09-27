<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Meetings\StoreMeetingRequest;
use App\Models\Mahalla;
use App\Models\User;
use App\Models\YouthMeeting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class YouthMeetingController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(YouthMeeting::class, 'meeting');
    }

    public function index(Request $request): Response
    {
        $meetings = YouthMeeting::with(['mahalla', 'chairman'])
            ->withCount('appeals')
            ->when($request->search, fn ($q, $s) => $q->whereLike('location', "%{$s}%"))
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->orderByDesc('meeting_date')
            ->paginate(25);

        return Inertia::render('Meetings/Index', [
            'meetings' => $meetings,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Meetings/Create', $this->formData());
    }

    public function store(StoreMeetingRequest $request): RedirectResponse
    {
        $meeting = YouthMeeting::create([
            ...$request->validated(),
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('meetings.show', $meeting->id)
            ->with('success', 'Учрашув яратилди.');
    }

    public function show(YouthMeeting $meeting): Response
    {
        $meeting->load(['mahalla', 'chairman', 'creator', 'appeals.category']);

        return Inertia::render('Meetings/Show', ['meeting' => $meeting]);
    }

    public function edit(YouthMeeting $meeting): Response
    {
        return Inertia::render('Meetings/Edit', [
            ...$this->formData(),
            'meeting' => $meeting,
        ]);
    }

    public function update(StoreMeetingRequest $request, YouthMeeting $meeting): RedirectResponse
    {
        $meeting->update($request->validated());

        return redirect()->route('meetings.show', $meeting->id)
            ->with('success', 'Учрашув янгиланди.');
    }

    public function destroy(YouthMeeting $meeting): RedirectResponse
    {
        $meeting->delete();

        return redirect()->route('meetings.index')
            ->with('success', 'Учрашув ўчирилди.');
    }

    /** @return array<string, mixed> */
    private function formData(): array
    {
        return [
            'users' => User::orderBy('name')->get(['id', 'name']),
            'mahallas' => Mahalla::orderBy('name_cyr')->get(['id', 'district_id', 'name_cyr']),
        ];
    }
}
