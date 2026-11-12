<?php

namespace App\Http\Controllers;

use Illuminate\View\View;
use App\Models\Group;
use App\Models\Subject;

class DashboardController extends Controller
{
    public function index(): View
    {
        // Показываем все группы, созданные учителем
        $groups = Group::withCount('students')->orderBy('name')->get();
        return view('dashboard', compact('groups'));
    }

    public function showGroup(Group $group): View
    {
        // Показываем список предметов (журналов) для этой группы
        $subjects = Subject::whereHas('schedules', function($q) use ($group) {
            $q->where('group_id', $group->id);
        })->get();
        
        // Или, если журналов еще нет, список будет пустым
        return view('group', compact('group', 'subjects'));
    }
}