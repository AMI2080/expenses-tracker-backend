<?php

namespace App\Http\Controllers;

use App\Models\ExpenseCenter;
use App\Models\ExpenseGroup;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class ExpenseCenterController extends Controller
{
    /**
     * Display a listing of expense centers the user has access to.
     * (Either owned through groups or as a group member)
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        // Get groups user owns or is a member of
        $ownedGroupIds = ExpenseGroup::where('owner_id', $user->id)->pluck('id');
        $memberGroupIds = $user->groups()->pluck('expense_groups.id');
        $allGroupIds = $ownedGroupIds->merge($memberGroupIds)->unique();

        // Get all centers in those groups
        $centers = ExpenseCenter::whereIn('group_id', $allGroupIds)
            ->with('group')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $centers,
        ]);
    }

    /**
     * Store a newly created expense center.
     * User must be the owner of the group.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'group_id' => 'required|exists:expense_groups,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();
        $group = ExpenseGroup::findOrFail($request->group_id);

        // Check if user is the owner of the group
        if ($group->owner_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. You must be the owner of the group to create a center.',
            ], 403);
        }

        $center = ExpenseCenter::create([
            'name' => $request->name,
            'group_id' => $request->group_id,
        ]);

        return response()->json([
            'success' => true,
            'data' => $center->load('group'),
        ], 201);
    }

    /**
     * Display the specified expense center.
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        $center = ExpenseCenter::with(['group', 'expenses'])
            ->findOrFail($id);

        // Check if user has access (owner of group or member of group)
        $isGroupOwner = $center->group->owner_id === $user->id;
        $isGroupMember = $center->group->members()->where('users.id', $user->id)->exists();
        $hasAccess = $isGroupOwner || $isGroupMember;

        if (!$hasAccess) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. You do not have access to this center.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $center,
        ]);
    }

    /**
     * Update the specified expense center.
     * User must be the owner of the group.
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
        $center = ExpenseCenter::with('group')->findOrFail($id);

        // Check if user is the owner of the group
        if ($center->group->owner_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only the group owner can update this center.',
            ], 403);
        }

        $center->update([
            'name' => $request->name,
        ]);

        return response()->json([
            'success' => true,
            'data' => $center->load('group'),
        ]);
    }

    /**
     * Remove the specified expense center.
     * User must be the owner of the group.
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        $center = ExpenseCenter::with('group')->findOrFail($id);

        // Check if user is the owner of the group
        if ($center->group->owner_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only the group owner can delete this center.',
            ], 403);
        }

        $center->delete();

        return response()->json([
            'success' => true,
            'message' => 'Expense center deleted successfully.',
        ]);
    }

    /**
     * Get detailed report of expenses for a specific center.
     */
    public function expensesReport(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        $center = ExpenseCenter::with('group')->findOrFail($id);

        // Check if user has access (owner of group or member of group)
        $isGroupOwner = $center->group->owner_id === $user->id;
        $isGroupMember = $center->group->members()->where('users.id', $user->id)->exists();
        $hasAccess = $isGroupOwner || $isGroupMember;

        if (!$hasAccess) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. You do not have access to this center.',
            ], 403);
        }

        $expenses = $center->expenses()
            ->with('user')
            ->orderBy('spent_at', 'desc')
            ->get();

        $totalAmount = $expenses->sum('amount');
        $expenseCount = $expenses->count();

        return response()->json([
            'success' => true,
            'data' => [
                'center' => $center->load('group'),
                'expenses' => $expenses,
                'summary' => [
                    'total_amount' => $totalAmount,
                    'expense_count' => $expenseCount,
                    'average_amount' => $expenseCount > 0 ? $totalAmount / $expenseCount : 0,
                ],
            ],
        ]);
    }
}
