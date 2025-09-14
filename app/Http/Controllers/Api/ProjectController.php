<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\CreateProjectRequest;
use App\Http\Requests\JoinProjectRequest;
use App\Models\Project;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProjectController extends Controller
{

    private function generateUniqueInvite()
{
    do {
        $code = Str::random(8);
    } while (Project::where('invite_code', $code)->exists());

    return $code;
}

public function myProjects(Request $request)
{
    $user = $request->user();

    $projects = $user->projects()
        ->with(['members', 'owner'])
        ->get();

    return response()->json([
        'success' => true,
        'projects' => $projects
    ]);
}


        public function store(CreateProjectRequest $request)
    {
        $user = $request->user();

        return DB::transaction(function () use ($request, $user) {
            $invite = $request->input('invite_code') ?? $this->generateUniqueInvite();

            $project = Project::create([
                'name' => $request->input('name'),
                'owner_id' => $user->id,
                'invite_code' => $invite,
            ]);

            $project->members()->attach($user->id, ['role' => 'owner']);

            $project->load('owner', 'members');

            return response()->json([
                'success' => true,
                'project' => $project
            ]);
        });
    }

        public function joinByInvite(JoinProjectRequest $request)
    {
        $user = $request->user();
        $invite = $request->input('invite_code');

        $project = Project::where('invite_code', $invite)->firstOrFail();

        $exists = $project->members()->where('user_id', $user->id)->exists();
        if ($exists) {
            return response()->json([
                'sucess' => false,
                'message' => 'Already joined'
            ], 409);
        }

        try {
            $project->members()->attach($user->id, ['role' => 'member']);
        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Already joined'
            ], 409);
        }

        $project->load('owner', 'members');


        return response()->json([
            'success' => true,
            'message' => 'Joined successfully',
            'project' => $project,
        ], 200);
    }


}
