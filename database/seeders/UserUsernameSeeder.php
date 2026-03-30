<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserUsernameSeeder extends Seeder
{
    /**
     * Standardize usernames and reset passwords for existing users.
     */
    public function run()
    {
        $users = User::query()->orderBy('id')->get();
        $used = [];

        $superAdminId = User::query()
            ->where('user_type', 'super_admin')
            ->orderBy('id')
            ->value('id');

        foreach ($users as $user) {
            $target = $this->desiredUsername($user, $superAdminId);
            $unique = $this->ensureUniqueUsername($target, $used);

            $user->username = $unique;
            $user->password = Hash::make('password');
            $user->save();

            $used[$unique] = true;
        }
    }

    protected function desiredUsername(User $user, $superAdminId): string
    {
        $current = trim((string) $user->username);
        $email = trim((string) $user->email);
        $phone = preg_replace('/\s+/', '', trim((string) $user->phone));

        if ($superAdminId && (int) $user->id === (int) $superAdminId) {
            return 'superadmin';
        }

        if ($user->user_type === 'parent' && $phone !== '') {
            return $phone;
        }

        if ($current !== '') {
            return Str::lower($current);
        }

        if ($email !== '' && strpos($email, '@') !== false) {
            $prefix = Str::before($email, '@');
            $prefix = trim(Str::lower($prefix));
            if ($prefix !== '') {
                return $prefix;
            }
        }

        $nameSeed = trim(Str::lower(preg_replace('/[^a-z0-9]+/i', '.', (string) $user->name)), '.');
        if ($nameSeed !== '') {
            return $nameSeed;
        }

        return 'user' . $user->id;
    }

    protected function ensureUniqueUsername(string $base, array $used): string
    {
        $base = trim(Str::lower($base));
        if ($base === '') {
            $base = 'user';
        }

        $candidate = $base;
        $i = 1;
        while (isset($used[$candidate])) {
            $candidate = $base . $i;
            $i++;
        }

        return $candidate;
    }
}

