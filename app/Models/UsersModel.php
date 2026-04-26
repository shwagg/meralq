<?php

namespace App\Models;

use CodeIgniter\Model;

class UsersModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'fullname',
        'username',
        'email',
        'password',
        'role',
        'createdAt',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';

    protected $validationRules = [
        'fullname' => 'required|min_length[3]|max_length[120]',
        'username' => 'required|min_length[3]|max_length[60]',
        'email'    => 'required|valid_email|max_length[120]',
        'password' => 'required',
        'role'     => 'required|in_list[admin,user]',
    ];

    public function findByCredential(string $credential): ?array
    {
        return $this->groupStart()
            ->where('username', $credential)
            ->orWhere('email', $credential)
            ->groupEnd()
            ->first();
    }

    public function listForAdmin(): array
    {
        return $this->select('id, fullname, username, email, role, createdAt')
            ->orderBy('createdAt', 'DESC')
            ->findAll();
    }
}
