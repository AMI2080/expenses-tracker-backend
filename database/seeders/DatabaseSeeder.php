<?php

namespace Database\Seeders;

use App\Models\GroupMember;
use App\Models\Expense;
use App\Models\ExpenseCenter;
use App\Models\ExpenseGroup;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create Admin User
        $admin = User::create([
            'name' => 'abdelrhman',
            'email' => 'kingawy2080@gmail.com',
            'password' => Hash::make('password'),
            'is_admin' => true,
            'is_approved' => true,
            'email_verified_at' => now(),
        ]);

        // Create Users
        $user1 = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => Hash::make('password'),
            'is_admin' => false,
            'is_approved' => false,
        ]);

        $user2 = User::create([
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'password' => Hash::make('password'),
            'is_admin' => false,
            'is_approved' => false,
        ]);

        $user3 = User::create([
            'name' => 'Bob Johnson',
            'email' => 'bob@example.com',
            'password' => Hash::make('password'),
            'is_admin' => false,
            'is_approved' => false,
        ]);

        // Create Expense Groups
        $workGroup = ExpenseGroup::create([
            'name' => 'Work',
            'owner_id' => $user1->id,
        ]);

        $homeGroup = ExpenseGroup::create([
            'name' => 'Home',
            'owner_id' => $user1->id,
        ]);

        $personalGroup = ExpenseGroup::create([
            'name' => 'Personal',
            'owner_id' => $user2->id,
        ]);

        // Create Expense Centers
        $workFoodCenter = ExpenseCenter::create([
            'name' => 'Food',
            'group_id' => $workGroup->id,
        ]);

        $workTransportCenter = ExpenseCenter::create([
            'name' => 'Transport',
            'group_id' => $workGroup->id,
        ]);

        $homeUtilitiesCenter = ExpenseCenter::create([
            'name' => 'Utilities',
            'group_id' => $homeGroup->id,
        ]);

        $homeGroceriesCenter = ExpenseCenter::create([
            'name' => 'Groceries',
            'group_id' => $homeGroup->id,
        ]);

        $personalEntertainmentCenter = ExpenseCenter::create([
            'name' => 'Entertainment',
            'group_id' => $personalGroup->id,
        ]);

        // Add members to groups
        GroupMember::create([
            'group_id' => $workGroup->id,
            'user_id' => $user2->id,
            'role' => 'member',
        ]);

        GroupMember::create([
            'group_id' => $workGroup->id,
            'user_id' => $user3->id,
            'role' => 'member',
        ]);

        GroupMember::create([
            'group_id' => $homeGroup->id,
            'user_id' => $user2->id,
            'role' => 'member',
        ]);

        // Create Expenses
        Expense::create([
            'amount' => 25.50,
            'description' => 'Lunch at restaurant',
            'center_id' => $workFoodCenter->id,
            'user_id' => $user1->id,
            'spent_at' => now()->subDays(2),
        ]);

        Expense::create([
            'amount' => 15.00,
            'description' => 'Coffee and snacks',
            'center_id' => $workFoodCenter->id,
            'user_id' => $user2->id,
            'spent_at' => now()->subDays(1),
        ]);

        Expense::create([
            'amount' => 8.50,
            'description' => 'Breakfast',
            'center_id' => $workFoodCenter->id,
            'user_id' => $user3->id,
            'spent_at' => now()->subDays(1),
        ]);

        Expense::create([
            'amount' => 45.00,
            'description' => 'Taxi to client meeting',
            'center_id' => $workTransportCenter->id,
            'user_id' => $user1->id,
            'spent_at' => now()->subDays(3),
        ]);

        Expense::create([
            'amount' => 12.00,
            'description' => 'Bus fare',
            'center_id' => $workTransportCenter->id,
            'user_id' => $user2->id,
            'spent_at' => now()->subDays(2),
        ]);

        Expense::create([
            'amount' => 120.00,
            'description' => 'Electricity bill',
            'center_id' => $homeUtilitiesCenter->id,
            'user_id' => $user1->id,
            'spent_at' => now()->subDays(5),
        ]);

        Expense::create([
            'amount' => 85.50,
            'description' => 'Weekly groceries',
            'center_id' => $homeGroceriesCenter->id,
            'user_id' => $user1->id,
            'spent_at' => now()->subDays(4),
        ]);

        Expense::create([
            'amount' => 30.00,
            'description' => 'Milk and bread',
            'center_id' => $homeGroceriesCenter->id,
            'user_id' => $user2->id,
            'spent_at' => now()->subDays(1),
        ]);

        Expense::create([
            'amount' => 50.00,
            'description' => 'Movie tickets',
            'center_id' => $personalEntertainmentCenter->id,
            'user_id' => $user2->id,
            'spent_at' => now()->subDays(2),
        ]);

        Expense::create([
            'amount' => 35.00,
            'description' => 'Concert ticket',
            'center_id' => $personalEntertainmentCenter->id,
            'user_id' => $user2->id,
            'spent_at' => now()->subDays(6),
        ]);

        $this->command->info('Database seeded successfully!');
        $this->command->info('Created:');
        $this->command->info('- 1 Admin User (abdelrhman - kingawy2080@gmail.com)');
        $this->command->info('- 3 Users');
        $this->command->info('- 3 Expense Groups');
        $this->command->info('- 5 Expense Centers');
        $this->command->info('- 4 Group Members');
        $this->command->info('- 10 Expenses');
    }
}
