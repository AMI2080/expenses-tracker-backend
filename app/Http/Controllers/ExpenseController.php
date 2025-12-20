<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseCenter;
use App\Models\ExpenseGroup;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class ExpenseController extends Controller
{
    /**
     * Display a listing of expenses.
     * User can see expenses from centers they have access to through group membership.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        // Get groups user owns or is a member of
        $ownedGroupIds = ExpenseGroup::where('owner_id', $user->id)->pluck('id');
        $memberGroupIds = $user->groups()->pluck('expense_groups.id');
        $allGroupIds = $ownedGroupIds->merge($memberGroupIds)->unique();

        // Get all centers in those groups
        $accessibleCenterIds = ExpenseCenter::whereIn('group_id', $allGroupIds)->pluck('id');

        $expenses = Expense::whereIn('center_id', $accessibleCenterIds)
            ->with(['center.group', 'user'])
            ->orderBy('spent_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $expenses,
        ]);
    }

    /**
     * Store a newly created expense.
     * User can only post if they are the group owner OR listed in Group_Members.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:1000',
            'center_id' => 'required|exists:expense_centers,id',
            'spent_at' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();
        $center = ExpenseCenter::with('group')->findOrFail($request->center_id);

        // Check if user has permission: must be group owner OR group member
        $isGroupOwner = $center->group->owner_id === $user->id;
        $isGroupMember = $center->group->members()->where('users.id', $user->id)->exists();

        if (!$isGroupOwner && !$isGroupMember) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. You must be the group owner or a member of this group to add expenses.',
            ], 403);
        }

        $expense = Expense::create([
            'amount' => $request->amount,
            'description' => $request->description,
            'center_id' => $request->center_id,
            'user_id' => $user->id,
            'spent_at' => $request->spent_at,
        ]);

        return response()->json([
            'success' => true,
            'data' => $expense->load(['center.group', 'user']),
        ], 201);
    }

    /**
     * Display the specified expense.
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        $expense = Expense::with(['center.group', 'user'])->findOrFail($id);

        // Check if user has access to the center through group membership
        $center = $expense->center;
        $isGroupOwner = $center->group->owner_id === $user->id;
        $isGroupMember = $center->group->members()->where('users.id', $user->id)->exists();
        $hasAccess = $isGroupOwner || $isGroupMember;

        if (!$hasAccess) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. You do not have access to this expense.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $expense,
        ]);
    }

    /**
     * Update the specified expense.
     * Only the user who created it can update.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:1000',
            'spent_at' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();
        $expense = Expense::with(['center.group'])->findOrFail($id);

        // Check if user created this expense
        if ($expense->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. You can only update your own expenses.',
            ], 403);
        }

        $expense->update([
            'amount' => $request->amount,
            'description' => $request->description,
            'spent_at' => $request->spent_at,
        ]);

        return response()->json([
            'success' => true,
            'data' => $expense->load(['center.group', 'user']),
        ]);
    }

    /**
     * Remove the specified expense.
     * Only the user who created it can delete.
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        $expense = Expense::findOrFail($id);

        // Check if user created this expense
        if ($expense->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. You can only delete your own expenses.',
            ], 403);
        }

        $expense->delete();

        return response()->json([
            'success' => true,
            'message' => 'Expense deleted successfully.',
        ]);
    }
}
