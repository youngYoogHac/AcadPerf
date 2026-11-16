<?php

namespace App\Http\Controllers;

use App\Exports\JournalExport;
use App\Models\Attendance;
use App\Models\Group;
use App\Models\Mark;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\Subject;
use Maatwebsite\Excel\Facades\Excel;
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

    private function getFilteredData(Request $request, Group $group, Subject $subject)
    {
        // 1. Студенты
        $studentsQuery = Student::where('group_id', $group->id);
        
        if ($request->filled('search_student')) {
            $search = $request->input('search_student');
            $studentsQuery->where(function($q) use ($search) {
                $q->where('last_name', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%");
            });
        }
        if ($request->filled('filter_subgroup')) {
            $studentsQuery->where('subgroup', $request->filter_subgroup);
        }
        
        $students = $studentsQuery->get();

        // Сортировка
        $sort = $request->input('sort', 'name_asc');
        switch ($sort) {
            case 'name_desc':
                $students = $students->sortByDesc(fn($s) => mb_strtolower($s->last_name . ' ' . $s->first_name))->values();
                break;
            case 'id_asc':
                $students = $students->sortBy('id')->values();
                break;
            case 'id_desc':
                $students = $students->sortByDesc('id')->values();
                break;
            case 'name_asc':
            default:
                $students = $students->sortBy(fn($s) => mb_strtolower($s->last_name . ' ' . $s->first_name))->values();
                break;
        }

        // 2. Пары (даты)
        $schedulesQuery = Schedule::where('group_id', $group->id)
            ->where('subject_id', $subject->id)
            ->orderBy('date')
            ->orderBy('pair_number');

        if ($request->filled('filter_type')) {
            if ($request->filter_type === 'single' && $request->filled('filter_date')) {
                $schedulesQuery->whereDate('date', $request->filter_date);
            } 
            elseif ($request->filter_type === 'range' && $request->filled('filter_date_from') && $request->filled('filter_date_to')) {
                $schedulesQuery->whereBetween('date', [$request->filter_date_from, $request->filter_date_to]);
            }
        }
        $schedules = $schedulesQuery->get();

        // 3. Оценки
        $attendances = Attendance::whereIn('schedule_id', $schedules->pluck('id'))
            ->whereIn('student_id', $students->pluck('id'))
            ->get()
            ->groupBy('student_id')
            ->map(fn($items) => $items->keyBy('schedule_id'));

        return compact('students', 'schedules', 'attendances');
    }

    public function grid(Request $request, Group $group, Subject $subject): View
    {
        $data = $this->getFilteredData($request, $group, $subject);
        $marks = Mark::all();
        
        $lessonTypes = \App\Models\LessonType::all(); 

        $subgroups = Student::where('group_id', $group->id)
            ->whereNotNull('subgroup')
            ->where('subgroup', '!=', '')
            ->distinct()
            ->orderBy('subgroup')
            ->pluck('subgroup');

        return view('journal.grid', array_merge($data, [
            'group' => $group, 
            'subject' => $subject, 
            'marks' => $marks,
            'subgroups' => $subgroups,
            'lessonTypes' => $lessonTypes
        ]));
    }

    public function updateGrid(Request $request)
    {
        // 1. СОХРАНЯЕМ ПОДГРУППЫ (ДОБАВЛЕНО)
        if ($request->has('subgroups')) {
            foreach ($request->input('subgroups', []) as $studentId => $subgroupValue) {
                Student::where('id', $studentId)->update(['subgroup' => trim($subgroupValue)]);
            }
        }

        // 2. СОХРАНЯЕМ ОЦЕНКИ (без изменений)
        foreach ($request->input('marks', []) as $studentId => $scheduleMarks) {
            foreach ($scheduleMarks as $scheduleId => $markName) {
                $markName = trim($markName);

                // Если пусто или прочерк — удаляем оценку
                if (empty($markName) || $markName === '—' || $markName === '-') {
                    Attendance::where('student_id', $studentId)->where('schedule_id', $scheduleId)->delete();
                    continue;
                }

                // Умное определение типа
                $type = (preg_match('/[0-9\/]/', $markName)) ? 'grade' : 'attendance';

                // Ищем оценку в базе, если нет — создаем новую
                $mark = Mark::firstOrCreate(
                    ['name' => $markName],
                    ['type' => $type]
                );

                Attendance::updateOrCreate(
                    ['student_id' => $studentId, 'schedule_id' => $scheduleId],
                    ['mark_id' => $mark->id]
                );
            }
        }
        
        return back()->with('success', 'Ведомость сохранена!');
    }

        public function import(Request $request, Group $group, Subject $subject)
    {
        $request->validate([
            'excel_file' => 'required|mimes:xlsx,xls,csv|max:2048'
        ]);

        $import = new \App\Imports\JournalImport($group, $subject);
        \Maatwebsite\Excel\Facades\Excel::import($import, $request->file('excel_file'));

        // ПРОВЕРКА: нашелся ли нужный лист?
        if (!$import->sheetFound) {
            return back()->with('error', "Ошибка: В файле не найден лист с названием «{$group->name}». Пожалуйста, убедитесь, что лист в Excel назван точно так же, как группа.");
        }

        $message = "Успешно обновлено оценок: {$import->savedCount}.";
        
        if (count($import->skippedMarks) > 0) {
            $message .= " Неизвестные отметки: " . implode(', ', array_unique($import->skippedMarks)) . ".";
        }

        return back()->with('success', $message);
    }


    public function export(Request $request, Group $group, Subject $subject)
    {
        $data = $this->getFilteredData($request, $group, $subject);
        $fileName = 'Журнал_' . $group->name . '_' . $subject->name . '.xlsx';
        
        return Excel::download(
            new JournalExport($group, $subject, $data['students'], $data['schedules'], $data['attendances']), 
            $fileName
        );
    }

    public function downloadTemplate(Request $request, Group $group, Subject $subject)
    {
        $data = $this->getFilteredData($request, $group, $subject);
        $fileName = 'Шаблон_' . $group->name . '_' . $subject->name . '.xlsx';
        
        return Excel::download(
            new \App\Exports\JournalTemplateExport($group, $subject, $data['students'], $data['schedules']), 
            $fileName
        );
    }

    public function storeSubject(Request $request, Group $group)
    {
        $request->validate(['name' => 'required|string|max:100']);
        
        // Создаем новый предмет (журнал)
        $subject = Subject::firstOrCreate(['name' => $request->name]);
        
        // Перенаправляем сразу в созданный журнал для загрузки Excel
        return redirect()->route('journal.grid', [$group->id, $subject->id])
            ->with('success', 'Журнал создан. Загрузите Excel-файл для начала работы.');
    }

    public function destroySubject(Group $group, Subject $subject)
    {
        // Находим все пары этой группы по этому предмету и удаляем их
        $schedules = Schedule::where('group_id', $group->id)
            ->where('subject_id', $subject->id)
            ->get();

        foreach ($schedules as $schedule) {
            $schedule->delete(); // Удалит и оценки (attendances) благодаря каскаду
        }

        // Возвращаемся на страницу группы
        return redirect()->route('groups.show', $group->id)->with('success', "Журнал по предмету «{$subject->name}» удалён.");
    }

    public function storeSchedule(Request $request, Group $group, Subject $subject)
    {
        $request->validate([
            'date' => 'required|date', 
            'pair_number' => 'required|integer',
            'lesson_type_id' => 'required|exists:lesson_types,id'
        ]);
        
        $schedule = Schedule::firstOrCreate(
            [
                'group_id' => $group->id,
                'subject_id' => $subject->id,
                'date' => $request->date,
                'pair_number' => $request->pair_number
            ],
            [
                'user_id' => auth()->id() ?? 1,
                'lesson_type_id' => $request->lesson_type_id,
            ]
        );

        $schedule->load('lessonType');

        return response()->json(['success' => true, 'schedule' => $schedule]);
    }

        public function updateSchedule(Request $request, Schedule $schedule)
    {
        $request->validate(['lesson_type_id' => 'required|exists:lesson_types,id']);
        
        $schedule->update(['lesson_type_id' => $request->lesson_type_id]);
        
        return response()->json(['success' => true]);
    }

    public function updateScheduleType(Request $request, Schedule $schedule)
    {
        $request->validate(['lesson_type_id' => 'required|exists:lesson_types,id']);
        $schedule->update(['lesson_type_id' => $request->lesson_type_id]);
        return response()->json(['success' => true]);
    }

    public function destroySchedule(Schedule $schedule)
    {
        $schedule->delete(); // Оценки удалятся автоматически благодаря cascadeOnDelete
        return response()->json(['success' => true]);
    }
}