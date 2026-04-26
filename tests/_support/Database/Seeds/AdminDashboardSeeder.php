<?php

namespace Tests\Support\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AdminDashboardSeeder extends Seeder
{
    public function run(): void
    {
        $this->db->table('users')->insertBatch([
            [
                'id'        => 1,
                'fullname'  => 'System Administrator',
                'username'  => 'admin',
                'email'     => 'admin@test.com',
                'password'  => password_hash('secret123', PASSWORD_DEFAULT),
                'role'      => 'admin',
                'createdAt' => '2026-04-26 10:00:00',
            ],
            [
                'id'        => 2,
                'fullname'  => 'Regular User',
                'username'  => 'user1',
                'email'     => 'user1@test.com',
                'password'  => password_hash('secret123', PASSWORD_DEFAULT),
                'role'      => 'user',
                'createdAt' => '2026-04-26 10:05:00',
            ],
        ]);

        $this->db->table('audit_logs')->insert([
            'userId'      => 1,
            'action'      => 'login',
            'description' => 'System Administrator signed in.',
            'createdAt'   => '2026-04-26 10:10:00',
        ]);

        $this->db->table('clients')->insert([
            'id'        => 1,
            'name'      => 'Sample Client',
            'address'   => '123 Demo Street',
            'createdBy' => 2,
            'createdAt' => '2026-04-26 10:15:00',
        ]);

        $this->db->table('bills')->insert([
            'id'             => 1,
            'userId'         => 2,
            'clientId'       => 1,
            'kw_consumption' => 250.00,
            'total_amount'   => 2650.00,
            'createdAt'      => '2026-04-26 10:20:00',
        ]);

        $this->db->table('bill_breakdown')->insertBatch([
            [
                'bill_id'              => 1,
                'range_from'           => 1,
                'range_to'             => 200,
                'rate_per_kw'          => 10.00,
                'consumption_in_range' => 200.00,
                'subtotal'             => 2000.00,
            ],
            [
                'bill_id'              => 1,
                'range_from'           => 201,
                'range_to'             => 500,
                'rate_per_kw'          => 13.00,
                'consumption_in_range' => 50.00,
                'subtotal'             => 650.00,
            ],
        ]);

        $this->db->table('audit_logs')->insert([
            'userId'      => 2,
            'action'      => 'compute_bill',
            'description' => 'Computed bill for Sample Client at 250.00 KW totaling PHP 2650.00.',
            'createdAt'   => '2026-04-26 10:21:00',
        ]);
    }
}