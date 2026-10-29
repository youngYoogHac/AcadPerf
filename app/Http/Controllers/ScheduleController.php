<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\LessonType;
use App\Models\Schedule;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    public function index(): View
    {
        $schedules = Schedule::with(['group', 'teacher', 'subject', 'lessonType'])
            ->orderBy('date', 'desc')->orderBy('pair_number')->get();
            
        return view('admin.schedules.index', compact('schedules'));
    }

    public function create(): View
    {
        $groups = Group::orderBy('name')->get();
        $teachers = User::whereHas('role', fn($q) => $q->where('name', 'teacher'))->orderBy('name')->get();
        $subjects = Subject::orderBy('name')->get();
        $lessonTypes = LessonType::all();

        return view('admin.schedules.create', compact('groups', 'teachers', 'subjects', 'lessonTypes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'group_id'       => 'required|exists:groups,id',
            'user_id'        => 'required|exists:users,id',
            'subject_id'     => 'required|exists:subjects,id',
            'lesson_type_id' => 'required|exists:lesson_types,id',
            'date'           => 'required|date',
            'pair_number'    => 'required|integer|min:1|max:6',
        ]);

        Schedule::create($validated);

        return redirect()->route('schedules.index')->with('success', 'Пара добавлена в расписание.');
    }

    public function destroy(Schedule $schedule): RedirectResponse
    {
        $schedule->delete();
        return back()->with('success', 'Пара удалена из расписания.');
    }

    public function teacherSchedule(): View
    {
        $schedules = Schedule::with(['group', 'subject', 'lessonType'])
            ->where('user_id', auth()->id())
            ->orderBy('date', 'desc')
            ->get();

        $uniqueJournals = $schedules->unique(function ($item) {
            return $item->group_id . '-' . $item->subject_id;
        });

        return view('teacher.schedule', compact('schedules', 'uniqueJournals'));
    }
}