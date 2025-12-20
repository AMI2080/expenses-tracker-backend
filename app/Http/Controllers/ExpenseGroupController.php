<?php

namespace App\Http\Controllers;

use App\Models\ExpenseGroup;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class ExpenseGroupController extends Controller
{
    /**
     * Display a listing of groups owned by the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $groups = ExpenseGroup::where('owner_id', $user->id)
            ->with('centers')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $groups,
        ]);
    }

    /**
     * Store a newly created expense group.
     * Only the authenticated user can create a group (they become the owner).
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();
        $group = ExpenseGroup::create([
            'name' => $request->name,
            'owner_id' => $user->id,
        ]);

        return response()->json([
            'success' => true,
            'data' => $group->load('centers'),
        ], 201);
    }

    /**
     * Display the specified expense group.
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        $group = ExpenseGroup::with(['centers', 'owner'])
            ->findOrFail($id);

        // Check if user is the owner
        if ($group->owner_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. You are not the owner of this group.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $group,
        ]);
    }

    /**
     * Update the specified expense group.
     * Only the owner can update.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();
        $group = ExpenseGroup::findOrFail($id);

        // Only owner can update
        if ($group->owner_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only the owner can update this group.',
            ], 403);
        }

        $group->update([
            'name' => $request->name,
        ]);

        return response()->json([
            'success' => true,
            'data' => $group->load('centers'),
        ]);
    }

    /**
     * Remove the specified expense group.
     * Only the owner can delete.
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        $group = ExpenseGroup::findOrFail($id);

        // Only owner can delete
        if ($group->owner_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only the owner can delete this group.',
            ], 403);
        }

        $group->delete();

        return response()->json([
            'success' => true,
            'message' => 'Expense group deleted successfully.',
        ]);
    }
}
