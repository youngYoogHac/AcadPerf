<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Group;
use App\Models\Mark;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JournalController extends Controller
{
    public function edit(Schedule $schedule): View
    {
        if ($schedule->user_id !== auth()->id()) {
            abort(403, 'Вы не ведете эту пару.');
        }

        $students = Student::where('group_id', $schedule->group_id)->orderBy('last_name')->get();
        
        $attendances = Attendance::where('schedule_id', $schedule->id)->get()->keyBy('student_id');
        
        $marks = Mark::all();

        return view('journal.edit', compact('schedule', 'students', 'attendances', 'marks'));
    }

    public function update(Request $request, Schedule $schedule): RedirectResponse
    {
        if ($schedule->user_id !== auth()->id()) {
            abort(403);
        }

        $marksData = $request->input('marks', []);

        foreach ($marksData as $studentId => $markId) {
            if ($markId) {
                Attendance::updateOrCreate(
                    ['schedule_id' => $schedule->id, 'student_id' => $studentId],
                    ['mark_id' => $markId]
                );
            } else {
                Attendance::where('schedule_id', $schedule->id)
                    ->where('student_id', $studentId)
                    ->delete();
            }
        }

        return back()->with('success', 'Журнал успешно сохранен!');
    }

    public function grid(Group $group, Subject $subject): View
    {
        $schedules = Schedule::where('group_id', $group->id)
            ->where('subject_id', $subject->id)
            ->orderBy('date')
            ->get();

        $students = Student::where('group_id', $group->id)->orderBy('last_name')->get();

        $attendances = Attendance::whereIn('schedule_id', $schedules->pluck('id'))
            ->get()
            ->groupBy('student_id')
            ->map(fn($items) => $items->keyBy('schedule_id'));

        $marks = Mark::all();

        return view('journal.grid', compact('group', 'subject', 'schedules', 'students', 'attendances', 'marks'));
    }

    public function updateGrid(Request $request)
    {
        foreach ($request->input('marks', []) as $studentId => $scheduleMarks) {
            foreach ($scheduleMarks as $scheduleId => $markId) {
                if ($markId) {
                    Attendance::updateOrCreate(
                        ['student_id' => $studentId, 'schedule_id' => $scheduleId],
                        ['mark_id' => $markId]
                    );
                } else {
                    Attendance::where('student_id', $studentId)->where('schedule_id', $scheduleId)->delete();
                }
            }
        }
        return back()->with('success', 'Ведомость сохранена!');
    }
}