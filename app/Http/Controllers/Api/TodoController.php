<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Todo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TodoController extends Controller
{
    /**
     * index: Mengambil semua todo milik user yang sedang login.
     * (FUNGSI INI YANG TADI HILANG)
     */
    public function index()
    {
        // Ambil data todo berdasarkan user_id, urutkan dari yang terbaru
        $todos = Todo::where('user_id', Auth::id())->latest()->get();

        return response()->json($todos);
    }

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

        // Simpan ke database
        $todo = Todo::create([
            'user_id'         => Auth::id(),
            'title'           => $data['title'],
            'completed'       => $data['completed'] ?? false,
            'attachment_path' => $attachmentPath
        ]);

        return response()->json($todo, 201);
    }

    public function update(Request $request, Todo $todo)
    {
        // Cek kepemilikan dulu
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
            // Hapus file lama jika ada
            if ($todo->attachment_path) {
                Storage::disk('public')->delete($todo->attachment_path);
            }

            // Upload file baru
            $todo->attachment_path = $request->file('attachment')
                ->store('todo_attachments', 'public');
        }

        $todo->save();

        return $todo;
    }

    public function destroy(Todo $todo)
    {
        // Cek kepemilikan dulu
        $this->authorizeOwner($todo);

        // Hapus file fisik jika ada
        if ($todo->attachment_path) {
            Storage::disk('public')->delete($todo->attachment_path);
        }

        $todo->delete();

        return response()->noContent();
    }

    /**
     * FUNGSI TAMBAHAN: Untuk mengecek apakah user yang login adalah pemilik todo
     */
    public function authorizeOwner($todo)
    {
        // Jika ID pemilik todo TIDAK SAMA dengan ID user yang sedang login
        if ($todo->user_id !== Auth::id()) {
            abort(403, 'Anda tidak memiliki akses untuk mengubah data ini.');
        }
    }
}