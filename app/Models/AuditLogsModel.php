<?php

namespace App\Models;

use CodeIgniter\Model;

class AuditLogsModel extends Model
{
    protected $table            = 'audit_logs';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'userId',
        'action',
        'description',
        'createdAt',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';

    public function record(int $userId, string $action, string $description): bool
    {
        return $this->insert([
            'userId'      => $userId,
            'action'      => $action,
            'description' => $description,
            'createdAt'   => date('Y-m-d H:i:s'),
        ]) !== false;
    }

    public function latestWithUsers(int $limit = 50): array
    {
        return $this->select('audit_logs.id, audit_logs.action, audit_logs.description, audit_logs.createdAt, users.fullname, users.username, users.role')
            ->join('users', 'users.id = audit_logs.userId', 'left')
            ->orderBy('audit_logs.createdAt', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    public function latestForUser(int $userId, int $limit = 25): array
    {
        return $this->select('audit_logs.id, audit_logs.action, audit_logs.description, audit_logs.createdAt')
            ->where('audit_logs.userId', $userId)
            ->orderBy('audit_logs.createdAt', 'DESC')
            ->orderBy('audit_logs.id', 'DESC')
            ->limit($limit)
            ->findAll();
    }
}
