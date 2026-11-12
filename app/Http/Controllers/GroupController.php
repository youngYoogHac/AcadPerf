<?php

namespace App\Http\Controllers;

use App\Models\Group;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GroupController extends Controller
{
    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:50']);
        Group::create(['name' => $request->name]);
        return back()->with('success', 'Группа создана!');
    }

    public function destroy(Group $group)
{
    // Благодаря cascadeOnDelete в БД, удалятся и студенты, и расписание, и оценки
    $group->delete();

    return redirect()->route('dashboard')->with('success', "Группа {$group->name} и все её журналы удалены.");
}
}