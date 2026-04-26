<?php

namespace App\Models;

use CodeIgniter\Model;

class BillBreakdownModel extends Model
{
    protected $table            = 'bill_breakdown';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'bill_id',
        'range_from',
        'range_to',
        'rate_per_kw',
        'consumption_in_range',
        'subtotal',
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

    public function forBillIds(array $billIds): array
    {
        if ($billIds === []) {
            return [];
        }

        $rows = $this->whereIn('bill_id', $billIds)
            ->orderBy('bill_id', 'DESC')
            ->orderBy('range_from', 'ASC')
            ->findAll();

        $grouped = [];

        foreach ($rows as $row) {
            $grouped[(int) $row['bill_id']][] = $row;
        }

        return $grouped;
    }
}
