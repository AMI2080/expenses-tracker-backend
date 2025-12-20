<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AdminController extends Controller
{
    /**
     * Get list of users pending approval (verified email but not approved).
     */
    public function pendingUsers(Request $request): JsonResponse
    {
        $pendingUsers = User::whereNotNull('email_verified_at')
            ->where('is_approved', false)
            ->select('id', 'name', 'email', 'email_verified_at', 'created_at')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $pendingUsers,
        ]);
    }

    /**
     * Approve a user account.
     */
    public function approveUser(Request $request, string $id): JsonResponse
    {
        $user = User::findOrFail($id);

        if ($user->isApproved()) {
            return response()->json([
                'success' => false,
                'message' => 'User is already approved.',
            ], 400);
        }

        if (!$user->hasVerifiedEmail()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot approve user. Email not verified.',
            ], 400);
        }

        $user->update([
            'is_approved' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User approved successfully.',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'is_approved' => true,
            ],
        ]);
    }

    /**
     * Reject/deactivate a user account.
     */
    public function rejectUser(Request $request, string $id): JsonResponse
    {
        $user = User::findOrFail($id);

        // Prevent admin from rejecting themselves
        if ($user->id === $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot reject your own account.',
            ], 400);
        }

        // Prevent rejecting other admins
        if ($user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot reject admin accounts.',
            ], 400);
        }

        $user->update([
            'is_approved' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User rejected successfully.',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'is_approved' => false,
            ],
        ]);
    }
}

