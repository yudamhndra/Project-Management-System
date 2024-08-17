<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{
    public function index()
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $tasks = Task::with('project:id,title', 'user:id,name')->get()->map(function ($task) {
            return $this->formatTaskResponse($task);
        });
        return response()->json($tasks);
    }

    public function store(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|string',
            'deadline' => 'nullable|date',
            'project_id' => 'required|exists:projects,id'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $task = Task::create(array_merge(
            $request->all(),
            ['user_id' => Auth::id()]
        ));

        $task->load('project:id,title', 'user:id,name');
        return response()->json($this->formatTaskResponse($task), 201);
    }

    public function show($id)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $task = Task::with('project:id,title', 'user:id,name')->find($id);

        if (!$task) {
            return response()->json(['message' => 'Task not found'], 404);
        }

        return response()->json($this->formatTaskResponse($task));
    }

    public function update(Request $request, $id)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $task = Task::find($id);

        if (!$task) {
            return response()->json(['message' => 'Task not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|string',
            'deadline' => 'nullable|date',
            'project_id' => 'exists:projects,id',
            'assigned_to' => 'exists:users,id'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
        $task->update(array_merge(
            $request->except('assigned_to'),
            ['user_id' => $request->input('assigned_to')]
        ));

        $task->load('project:id,title', 'user:id,name');
        return response()->json($this->formatTaskResponse($task));
    }

    public function destroy($id)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $task = Task::find($id);

        if (!$task) {
            return response()->json(['message' => 'Task not found'], 404);
        }

        $task->delete();

        return response()->json(['message' => 'Task deleted successfully']);
    }

    private function formatTaskResponse($task)
    {
        return [
            'id' => $task->id,
            'project' => $task->project ? $task->project->title : '',
            'title' => $task->title,
            'description' => $task->description,
            'status' => $task->status,
            'deadline' => $task->deadline,
            'assigned_to' => $task->user ? $task->user->name : null
        ];
    }
}
