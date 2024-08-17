<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = Project::with('tasks:id,project_id,title,status')->get()->map(function ($project) {
            return $this->formatProjectResponse($project);
        });
        return response()->json($projects);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_at' => 'nullable|date',
            'end_at' => 'nullable|date|after_or_equal:start_at'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $project = Project::create($request->all());
        $project->load('tasks:id,project_id,title,status');
        return response()->json($this->formatProjectResponse($project), 201);
    }

    public function show($id)
    {
        $project = Project::with('tasks:id,project_id,title,status')->find($id);

        if (!$project) {
            return response()->json(['message' => 'Project not found'], 404);
        }

        return response()->json($this->formatProjectResponse($project));
    }

    public function update(Request $request, $id)
    {
        $project = Project::find($id);

        if (!$project) {
            return response()->json(['message' => 'Project not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'string|max:255',
            'description' => 'nullable|string',
            'start_at' => 'nullable|date',
            'end_at' => 'nullable|date|after_or_equal:start_at'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $project->update($request->all());
        $project->load('tasks:id,project_id,title,status');
        return response()->json($this->formatProjectResponse($project));
    }

    public function destroy($id)
    {
        $project = Project::find($id);

        if (!$project) {
            return response()->json(['message' => 'Project not found'], 404);
        }

        $project->delete();

        return response()->json(['message' => 'Project deleted successfully']);
    }

    private function formatProjectResponse($project)
    {
        return [
            'id' => $project->id,
            'title' => $project->title,
            'description' => $project->description,
            'start_at' => $project->start_at,
            'end_at' => $project->end_at,
            'tasks' => $project->tasks->map(function ($task) {
                return [
                    'id' => $task->id,
                    'title' => $task->title,
                    'status' => $task->status
                ];
            })
        ];
    }
}