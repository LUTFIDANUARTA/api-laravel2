<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Todo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TodoController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'title'      => 'required|string|max:100',
            'completed'  => 'boolean',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        // upload jika ada file
        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')
                ->store('todo_attachments', 'public');
        }

        $todo = Auth::user()->todos()->create([
            'title'           => $data['title'],
            'completed'       => $data['completed'] ?? false,
            'attachment_path' => $attachmentPath
        ]);

        return response()->json($todo, 201);
    }

    public function update(Request $request, Todo $todo)
    {
        $this->authorizeOwner($todo);

        $data = $request->validate([
            'title'      => 'sometimes|string|max:100',
            'completed'  => 'sometimes|boolean',
            'attachment' => 'sometimes|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        if (isset($data['title'])) {
            $todo->title = $data['title'];
        }
        if (isset($data['completed'])) {
            $todo->completed = $data['completed'];
        }

        if ($request->hasFile('attachment')) {
            if ($todo->attachment_path) {
                Storage::disk('public')->delete($todo->attachment_path);
            }

            $todo->attachment_path = $request->file('attachment')
                ->store('todo_attachments', 'public');
        }

        $todo->save();

        return $todo;
    }

    public function destroy(Todo $todo)
    {
        $this->authorizeOwner($todo);

        if ($todo->attachment_path) {
            Storage::disk('public')->delete($todo->attachment_path);
        }

        $todo->delete();

        return response()->noContent();
    }
}