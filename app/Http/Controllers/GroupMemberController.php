<?php

namespace App\Http\Controllers;

use App\Models\GroupMember;
use App\Models\ExpenseGroup;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class GroupMemberController extends Controller
{
    /**
     * Add/invite a user to an expense group.
     * Only the group owner can add members.
     */
    public function store(Request $request, string $group): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'role' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();
        $groupModel = ExpenseGroup::findOrFail($group);

        // Check if user is the owner of the group
        if ($groupModel->owner_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only the group owner can add members to a group.',
            ], 403);
        }

        // Check if user is already a member
        $existingMember = GroupMember::where('group_id', $group)
            ->where('user_id', $request->user_id)
            ->first();

        if ($existingMember) {
            return response()->json([
                'success' => false,
                'message' => 'User is already a member of this group.',
            ], 409);
        }

        // Prevent adding the group owner as a member (they already have access)
        if ($groupModel->owner_id == $request->user_id) {
            return response()->json([
                'success' => false,
                'message' => 'The group owner already has access to all centers in the group.',
            ], 409);
        }

        $member = GroupMember::create([
            'group_id' => $group,
            'user_id' => $request->user_id,
            'role' => $request->role ?? 'member',
        ]);

        return response()->json([
            'success' => true,
            'data' => $member->load(['group', 'user']),
        ], 201);
    }

    /**
     * Remove a member from an expense group.
     * Only the group owner can remove members.
     */
    public function destroy(Request $request, string $group, string $member): JsonResponse
    {
        $user = $request->user();
        $memberModel = GroupMember::with('group')->where('group_id', $group)->findOrFail($member);

        // Check if user is the owner of the group
        if ($memberModel->group->owner_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only the group owner can remove members from a group.',
            ], 403);
        }

        $memberModel->delete();

        return response()->json([
            'success' => true,
            'message' => 'Member removed successfully.',
        ]);
    }

    /**
     * List all members of a specific group.
     */
    public function index(Request $request, string $group): JsonResponse
    {
        $user = $request->user();
        $groupModel = ExpenseGroup::findOrFail($group);

        // Check if user has access (owner of group or member of group)
        $hasAccess = $groupModel->owner_id === $user->id 
            || $groupModel->members()->where('users.id', $user->id)->exists();

        if (!$hasAccess) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. You do not have access to this group.',
            ], 403);
        }

        $members = $groupModel->groupMembers()->with('user')->get();

        return response()->json([
            'success' => true,
            'data' => $members,
        ]);
    }
}
