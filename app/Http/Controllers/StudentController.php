<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentController extends Controller
{
    public function index(): View
    {
        $students = Student::with('group')->orderBy('last_name')->get();
        $groups = Group::orderBy('name')->get();
        
        return view('admin.students.index', compact('students', 'groups'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'group_id'    => 'required|exists:groups,id',
            'last_name'   => 'required|string|max:100',
            'first_name'  => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
        ]);

        Student::create($validated);

        return back()->with('success', 'Студент добавлен в группу.');
    }

    public function destroy(Student $student): RedirectResponse
    {
        $student->delete();
        return back()->with('success', 'Студент удален.');
    }
}