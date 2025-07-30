<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Contact;
use App\Models\User;

class ContactSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::first();

        $contacts = [
            ['name' => 'John Doe', 'email' => 'john@example.com', 'phone' => '1234567890', 'gender' => 'male'],
            ['name' => 'Jane Smith', 'email' => 'jane@example.com', 'phone' => '2345678901', 'gender' => 'female'],
            ['name' => 'Alice Johnson', 'email' => 'alice@example.com', 'phone' => '3456789012', 'gender' => 'female'],
            ['name' => 'Bob Brown', 'email' => 'bob@example.com', 'phone' => '4567890123', 'gender' => 'male'],
            ['name' => 'Charlie Davis', 'email' => 'charlie@example.com', 'phone' => '5678901234', 'gender' => 'male'],
            ['name' => 'Diana Evans', 'email' => 'diana@example.com', 'phone' => '6789012345', 'gender' => 'female'],
            ['name' => 'Ethan Foster', 'email' => 'ethan@example.com', 'phone' => '7890123456', 'gender' => 'male'],
            ['name' => 'Fiona Green', 'email' => 'fiona@example.com', 'phone' => '8901234567', 'gender' => 'female'],
            ['name' => 'George Harris', 'email' => 'george@example.com', 'phone' => '9012345678', 'gender' => 'male'],
            ['name' => 'Hannah Irving', 'email' => 'hannah@example.com', 'phone' => '0123456789', 'gender' => 'female'],
            ['name' => 'Ian Jackson', 'email' => 'ian@example.com', 'phone' => '1234509876', 'gender' => 'male'],
            ['name' => 'Julia King', 'email' => 'julia@example.com', 'phone' => '2345609876', 'gender' => 'female'],
            ['name' => 'Kevin Lee', 'email' => 'kevin@example.com', 'phone' => '3456709876', 'gender' => 'male'],
            ['name' => 'Laura Martin', 'email' => 'laura@example.com', 'phone' => '4567809876', 'gender' => 'female'],
            ['name' => 'Michael Nelson', 'email' => 'michael@example.com', 'phone' => '5678909876', 'gender' => 'male'],
        ];

        foreach ($contacts as $contact) {
            $contact['user_id'] = $user ? $user->id : null;
            Contact::create($contact);
        }
    }
}
