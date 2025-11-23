<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Todo;


class TodoController extends Controller
{
    public function index()
    {
        return Todo::orderByDesc('id')->get();
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'title' => 'required|string|max:100',
            'completed' => 'boolean'
        ]);

        return Todo::create($data);
    }

    public function show(Todo $todo)
    {
        return $todo;
    }

    public function update(Request $r, Todo $todo)
    {
        $data = $r->validate([
            'title' => 'sometimes|string|max:100',
            'completed' => 'sometimes|boolean'
        ]);

        $todo->update($data);
        return $todo;
    }

    public function destroy(Todo $todo)
    {
        $todo->delete();
        return response()->noContent();
    }
}