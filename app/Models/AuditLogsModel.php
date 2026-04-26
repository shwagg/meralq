<?php

namespace App\Models;

use CodeIgniter\Model;

class AuditLogsModel extends Model
{
    private const DEFAULT_PAGE_SIZE = 10;

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

    public function paginateWithUsers(int $page = 1, int $perPage = self::DEFAULT_PAGE_SIZE): array
    {
        $page    = max(1, $page);
        $perPage = max(1, $perPage);
        $offset  = ($page - 1) * $perPage;

        $builder = $this->db->table($this->table)
            ->select('audit_logs.id, audit_logs.action, audit_logs.description, audit_logs.createdAt, users.fullname, users.username, users.role')
            ->join('users', 'users.id = audit_logs.userId', 'left');

        $total = (clone $builder)->countAllResults();
        $items = $builder
            ->orderBy('audit_logs.createdAt', 'DESC')
            ->orderBy('audit_logs.id', 'DESC')
            ->limit($perPage, $offset)
            ->get()
            ->getResultArray();

        return [
            'items'      => $items,
            'total'      => $total,
            'page'       => $page,
            'perPage'    => $perPage,
            'pageCount'  => max(1, (int) ceil($total / $perPage)),
        ];
    }

    public function paginateForUser(int $userId, int $page = 1, int $perPage = self::DEFAULT_PAGE_SIZE): array
    {
        $page    = max(1, $page);
        $perPage = max(1, $perPage);
        $offset  = ($page - 1) * $perPage;

        $builder = $this->db->table($this->table)
            ->select('audit_logs.id, audit_logs.action, audit_logs.description, audit_logs.createdAt')
            ->where('audit_logs.userId', $userId);

        $total = (clone $builder)->countAllResults();
        $items = $builder
            ->orderBy('audit_logs.createdAt', 'DESC')
            ->orderBy('audit_logs.id', 'DESC')
            ->limit($perPage, $offset)
            ->get()
            ->getResultArray();

        return [
            'items'      => $items,
            'total'      => $total,
            'page'       => $page,
            'perPage'    => $perPage,
            'pageCount'  => max(1, (int) ceil($total / $perPage)),
        ];
    }
}
