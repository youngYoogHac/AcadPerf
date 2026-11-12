<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function store(Request $request, Group $group)
    {
        $request->validate(['full_name' => 'required|string|max:255']);
        
        $parts = explode(' ', trim($request->input('full_name')));
        
        $student = Student::create([
            'group_id'    => $group->id,
            // Автоматически делаем первую букву заглавной!
            'last_name'   => mb_convert_case($parts[0] ?? 'Неизвестно', MB_CASE_TITLE, "UTF-8"),
            'first_name'  => mb_convert_case($parts[1] ?? '', MB_CASE_TITLE, "UTF-8"),
            'middle_name' => mb_convert_case($parts[2] ?? '', MB_CASE_TITLE, "UTF-8"),
        ]);

        return response()->json(['success' => true, 'student' => $student]);
    }

    public function update(Request $request, Student $student)
    {
        $request->validate(['full_name' => 'required|string|max:255']);
        
        $parts = explode(' ', trim($request->input('full_name')));
        
        $student->update([
            'last_name'   => mb_convert_case($parts[0] ?? 'Неизвестно', MB_CASE_TITLE, "UTF-8"),
            'first_name'  => mb_convert_case($parts[1] ?? '', MB_CASE_TITLE, "UTF-8"),
            'middle_name' => mb_convert_case($parts[2] ?? '', MB_CASE_TITLE, "UTF-8"),
        ]);

        return response()->json(['success' => true]);
    }

    public function destroy(Student $student)
    {
        // Благодаря каскадному удалению, его оценки тоже удалятся
        $student->delete();
        
        return response()->json(['success' => true]);
    }
}