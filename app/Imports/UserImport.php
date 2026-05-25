<?php
namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;

class UserImport implements ToModel, WithStartRow
{
    public function startRow(): int { return 2; }

    public function model(array $row)
    {
        return User::create([
            'name' => $row[0] ?? '',
            'email' => $row[1] ?? '',
            'password' => Hash::make($row[4] ?? 'password'),
        ]);
    }
}
