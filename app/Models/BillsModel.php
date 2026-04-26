<?php

namespace App\Models;

use CodeIgniter\Model;

class BillsModel extends Model
{
    protected $table            = 'bills';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'userId',
        'clientId',
        'kw_consumption',
        'total_amount',
        'createdAt',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    public function historyForUser(int $userId, int $limit = 25): array
    {
        return $this->select('bills.id, bills.userId, bills.clientId, bills.kw_consumption, bills.total_amount, bills.createdAt, clients.name AS client_name, clients.address AS client_address')
            ->join('clients', 'clients.id = bills.clientId', 'left')
            ->where('bills.userId', $userId)
            ->orderBy('bills.createdAt', 'DESC')
            ->orderBy('bills.id', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    public function countForUser(int $userId): int
    {
        return $this->where('userId', $userId)->countAllResults();
    }
}
