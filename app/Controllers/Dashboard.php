<?php

namespace App\Controllers;

use App\Models\AuditLogsModel;
use App\Models\BillBreakdownModel;
use App\Models\BillsModel;
use App\Models\ClientsModel;
use App\Models\UsersModel;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;
use RuntimeException;

class Dashboard extends BaseController
{
    private UsersModel $usersModel;

    private AuditLogsModel $auditLogsModel;

    private ClientsModel $clientsModel;

    private BillsModel $billsModel;

    private BillBreakdownModel $billBreakdownModel;

    public function __construct()
    {
        $this->usersModel          = new UsersModel();
        $this->auditLogsModel      = new AuditLogsModel();
        $this->clientsModel        = new ClientsModel();
        $this->billsModel          = new BillsModel();
        $this->billBreakdownModel  = new BillBreakdownModel();
    }

    public function index()
    {
        $redirect = $this->redirectAuthenticatedUser();

        if ($redirect !== null) {
            return $redirect;
        }

        return redirect()->to('/login');
    }

    public function admin(): string|RedirectResponse
    {
        $user = $this->requireRole('admin');

        if ($user instanceof RedirectResponse) {
            return $user;
        }

        return view('dashboard/admin', [
            'pageTitle' => 'Admin Dashboard',
            'user'      => $user,
            'users'     => $this->usersModel->listForAdmin(),
            'auditLogs' => $this->auditLogsModel->latestWithUsers(),
        ]);
    }

    public function adminUsers(): ResponseInterface|RedirectResponse
    {
        $admin = $this->requireRole('admin');

        if ($admin instanceof RedirectResponse) {
            return $admin;
        }

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => $this->usersModel->listForAdmin(),
        ]);
    }

    public function createUser(): ResponseInterface|RedirectResponse
    {
        $admin = $this->requireRole('admin');

        if ($admin instanceof RedirectResponse) {
            return $admin;
        }

        $payload = $this->userPayload();

        $rules = [
            'fullname' => 'required|min_length[3]|max_length[120]',
            'username' => 'required|min_length[3]|max_length[60]|is_unique[users.username]',
            'email'    => 'required|valid_email|max_length[120]|is_unique[users.email]',
            'password' => 'required|min_length[8]',
            'role'     => 'required|in_list[admin,user]',
        ];

        if (! $this->validateData($payload, $rules)) {
            return $this->response->setStatusCode(422)->setJSON([
                'status'  => 'error',
                'message' => 'Please correct the user details and try again.',
                'errors'  => $this->validator->getErrors(),
            ]);
        }

        $created = $this->usersModel->insert([
            'fullname'  => $payload['fullname'],
            'username'  => $payload['username'],
            'email'     => $payload['email'],
            'password'  => password_hash($payload['password'], PASSWORD_DEFAULT),
            'role'      => $payload['role'],
            'createdAt' => date('Y-m-d H:i:s'),
        ]);

        if ($created === false) {
            return $this->response->setStatusCode(500)->setJSON([
                'status'  => 'error',
                'message' => 'Unable to create the user account.',
            ]);
        }

        $this->auditLogsModel->record(
            (int) $admin['id'],
            'create_user',
            sprintf('Created user account for %s (%s).', $payload['fullname'], $payload['role'])
        );

        return $this->response->setStatusCode(201)->setJSON([
            'status'  => 'success',
            'message' => 'User account created.',
            'data'    => $this->dashboardPayload(),
        ]);
    }

    public function updateUser(int $id): ResponseInterface|RedirectResponse
    {
        $admin = $this->requireRole('admin');

        if ($admin instanceof RedirectResponse) {
            return $admin;
        }

        $existingUser = $this->usersModel->find($id);

        if ($existingUser === null) {
            return $this->response->setStatusCode(404)->setJSON([
                'status'  => 'error',
                'message' => 'User account not found.',
            ]);
        }

        $payload = $this->userPayload();

        $rules = [
            'fullname' => 'required|min_length[3]|max_length[120]',
            'username' => 'required|min_length[3]|max_length[60]|is_unique[users.username,id,' . $id . ']',
            'email'    => 'required|valid_email|max_length[120]|is_unique[users.email,id,' . $id . ']',
            'role'     => 'required|in_list[admin,user]',
        ];

        if ($payload['password'] !== '') {
            $rules['password'] = 'permit_empty|min_length[8]';
        }

        if (! $this->validateData($payload, $rules)) {
            return $this->response->setStatusCode(422)->setJSON([
                'status'  => 'error',
                'message' => 'Please correct the user details and try again.',
                'errors'  => $this->validator->getErrors(),
            ]);
        }

        $updateData = [
            'fullname' => $payload['fullname'],
            'username' => $payload['username'],
            'email'    => $payload['email'],
            'role'     => $payload['role'],
        ];

        if ($payload['password'] !== '') {
            $updateData['password'] = password_hash($payload['password'], PASSWORD_DEFAULT);
        }

        if (! $this->usersModel->update($id, $updateData)) {
            return $this->response->setStatusCode(500)->setJSON([
                'status'  => 'error',
                'message' => 'Unable to update the user account.',
            ]);
        }

        if ((int) $admin['id'] === $id) {
            $this->session->set([
                'fullname' => $updateData['fullname'],
                'username' => $updateData['username'],
                'email'    => $updateData['email'],
                'role'     => $updateData['role'],
            ]);
        }

        $this->auditLogsModel->record(
            (int) $admin['id'],
            'update_user',
            sprintf('Updated user account for %s.', $updateData['fullname'])
        );

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'User account updated.',
            'data'    => $this->dashboardPayload(),
        ]);
    }

    public function deleteUser(int $id): ResponseInterface|RedirectResponse
    {
        $admin = $this->requireRole('admin');

        if ($admin instanceof RedirectResponse) {
            return $admin;
        }

        if ((int) $admin['id'] === $id) {
            return $this->response->setStatusCode(422)->setJSON([
                'status'  => 'error',
                'message' => 'You cannot delete the currently signed-in admin account.',
            ]);
        }

        $existingUser = $this->usersModel->find($id);

        if ($existingUser === null) {
            return $this->response->setStatusCode(404)->setJSON([
                'status'  => 'error',
                'message' => 'User account not found.',
            ]);
        }

        if (! $this->usersModel->delete($id)) {
            return $this->response->setStatusCode(500)->setJSON([
                'status'  => 'error',
                'message' => 'Unable to delete the user account.',
            ]);
        }

        $this->auditLogsModel->record(
            (int) $admin['id'],
            'delete_user',
            sprintf('Deleted user account for %s.', $existingUser['fullname'])
        );

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'User account deleted.',
            'data'    => $this->dashboardPayload(),
        ]);
    }

    public function adminAuditLogs(): ResponseInterface|RedirectResponse
    {
        $admin = $this->requireRole('admin');

        if ($admin instanceof RedirectResponse) {
            return $admin;
        }

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => $this->auditLogsModel->latestWithUsers(),
        ]);
    }

    public function adminAuditTrail(): string|RedirectResponse
    {
        $admin = $this->requireRole('admin');

        if ($admin instanceof RedirectResponse) {
            return $admin;
        }

        $page = max(1, (int) ($this->request->getGet('page') ?? 1));

        return view('dashboard/admin_audit_trail', [
            'pageTitle'  => 'Admin Audit Trail',
            'user'       => $admin,
            'pagination' => $this->auditLogsModel->paginateWithUsers($page),
        ]);
    }

    public function user(): string|RedirectResponse
    {
        $user = $this->requireRole('user');

        if ($user instanceof RedirectResponse) {
            return $user;
        }

        return view('dashboard/user', [
            'pageTitle' => 'User Dashboard',
            'user'      => $user,
            'dashboard' => $this->userDashboardPayload((int) $user['id']),
        ]);
    }

    public function userDashboardData(): ResponseInterface|RedirectResponse
    {
        $user = $this->requireRole('user');

        if ($user instanceof RedirectResponse) {
            return $user;
        }

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => $this->userDashboardPayload((int) $user['id']),
        ]);
    }

    public function userAuditTrail(): string|RedirectResponse
    {
        $user = $this->requireRole('user');

        if ($user instanceof RedirectResponse) {
            return $user;
        }

        $page = max(1, (int) ($this->request->getGet('page') ?? 1));

        return view('dashboard/user_audit_trail', [
            'pageTitle'  => 'My Action Trail',
            'user'       => $user,
            'pagination' => $this->auditLogsModel->paginateForUser((int) $user['id'], $page),
        ]);
    }

    public function createBill(): ResponseInterface|RedirectResponse
    {
        $user = $this->requireRole('user');

        if ($user instanceof RedirectResponse) {
            return $user;
        }

        $payload = $this->billPayload();

        $rules = [
            'client_name'    => 'required|min_length[3]|max_length[120]',
            'client_address' => 'required|min_length[5]|max_length[255]',
            'kw_consumption' => 'required|decimal|greater_than[0]',
        ];

        if (! $this->validateData($payload, $rules)) {
            return $this->response->setStatusCode(422)->setJSON([
                'status'  => 'error',
                'message' => 'Please correct the billing details and try again.',
                'errors'  => $this->validator->getErrors(),
            ]);
        }

        $consumption = round((float) $payload['kw_consumption'], 2);
        $breakdown   = $this->calculateBillBreakdown($consumption);
        $totalAmount = array_sum(array_column($breakdown, 'subtotal'));
        $now         = date('Y-m-d H:i:s');
        $db          = db_connect();

        $db->transStart();

        $client = $this->clientsModel->firstOwnedByUser(
            (int) $user['id'],
            $payload['client_name'],
            $payload['client_address']
        );

        if ($client === null) {
            $clientId = $this->clientsModel->insert([
                'name'      => $payload['client_name'],
                'address'   => $payload['client_address'],
                'createdBy' => (int) $user['id'],
                'createdAt' => $now,
            ], true);

            if ($clientId === false) {
                $db->transRollback();

                return $this->response->setStatusCode(500)->setJSON([
                    'status'  => 'error',
                    'message' => 'Unable to save the client record.',
                ]);
            }
        } else {
            $clientId = (int) $client['id'];
        }

        $billId = $this->billsModel->insert([
            'userId'         => (int) $user['id'],
            'clientId'       => $clientId,
            'kw_consumption' => number_format($consumption, 2, '.', ''),
            'total_amount'   => number_format($totalAmount, 2, '.', ''),
            'createdAt'      => $now,
        ], true);

        if ($billId === false) {
            $db->transRollback();

            return $this->response->setStatusCode(500)->setJSON([
                'status'  => 'error',
                'message' => 'Unable to save the computed bill.',
            ]);
        }

        foreach ($breakdown as $row) {
            $created = $this->billBreakdownModel->insert([
                'bill_id'              => (int) $billId,
                'range_from'           => $row['range_from'],
                'range_to'             => $row['range_to'],
                'rate_per_kw'          => number_format($row['rate_per_kw'], 2, '.', ''),
                'consumption_in_range' => number_format($row['consumption_in_range'], 2, '.', ''),
                'subtotal'             => number_format($row['subtotal'], 2, '.', ''),
            ]);

            if ($created === false) {
                $db->transRollback();

                return $this->response->setStatusCode(500)->setJSON([
                    'status'  => 'error',
                    'message' => 'Unable to save the bill breakdown.',
                ]);
            }
        }

        $logged = $this->auditLogsModel->record(
            (int) $user['id'],
            'compute_bill',
            sprintf(
                'Computed bill for %s at %.2f KW totaling PHP %.2f.',
                $payload['client_name'],
                $consumption,
                $totalAmount
            )
        );

        if (! $logged) {
            $db->transRollback();

            return $this->response->setStatusCode(500)->setJSON([
                'status'  => 'error',
                'message' => 'Unable to record the billing action.',
            ]);
        }

        $db->transComplete();

        if (! $db->transStatus()) {
            throw new RuntimeException('Failed to finalize bill transaction.');
        }

        return $this->response->setStatusCode(201)->setJSON([
            'status'  => 'success',
            'message' => 'Electric bill computed successfully.',
            'data'    => $this->userDashboardPayload((int) $user['id']),
        ]);
    }

    private function userPayload(): array
    {
        return [
            'fullname' => trim((string) $this->request->getPost('fullname')),
            'username' => trim((string) $this->request->getPost('username')),
            'email'    => trim((string) $this->request->getPost('email')),
            'password' => (string) $this->request->getPost('password'),
            'role'     => trim((string) $this->request->getPost('role')),
        ];
    }

    private function billPayload(): array
    {
        return [
            'client_name'    => trim((string) $this->request->getPost('client_name')),
            'client_address' => trim((string) $this->request->getPost('client_address')),
            'kw_consumption' => trim((string) $this->request->getPost('kw_consumption')),
        ];
    }

    private function dashboardPayload(): array
    {
        return [
            'users'     => $this->usersModel->listForAdmin(),
            'auditLogs' => $this->auditLogsModel->latestWithUsers(),
        ];
    }

    private function userDashboardPayload(int $userId): array
    {
        $history     = $this->billsModel->historyForUser($userId);
        $breakdowns  = $this->billBreakdownModel->forBillIds(array_map(static fn(array $bill): int => (int) $bill['id'], $history));
        $auditLogs   = $this->auditLogsModel->latestForUser($userId);

        foreach ($history as &$bill) {
            $bill['breakdown'] = $breakdowns[(int) $bill['id']] ?? [];
        }

        unset($bill);

        return [
            'metrics' => [
                'billsCount'   => $this->billsModel->countForUser($userId),
                'clientsCount' => $this->clientsModel->countForUser($userId),
                'auditCount'   => count($auditLogs),
            ],
            'billingHistory' => $history,
            'auditLogs'      => $auditLogs,
            'rates'          => [
                ['label' => '1 - 200 KW', 'rate' => 10.00],
                ['label' => '201 - 500 KW', 'rate' => 13.00],
                ['label' => '501 KW and above', 'rate' => 15.00],
            ],
        ];
    }

    private function calculateBillBreakdown(float $consumption): array
    {
        $tiers = [
            ['range_from' => 1, 'range_to' => 200, 'rate_per_kw' => 10.00],
            ['range_from' => 201, 'range_to' => 500, 'rate_per_kw' => 13.00],
            ['range_from' => 501, 'range_to' => 0, 'rate_per_kw' => 15.00],
        ];

        $remaining = $consumption;
        $rows      = [];

        foreach ($tiers as $tier) {
            if ($remaining <= 0) {
                break;
            }

            $rangeLimit = $tier['range_to'] === 0
                ? $remaining
                : min($remaining, (float) ($tier['range_to'] - $tier['range_from'] + 1));

            if ($rangeLimit <= 0) {
                continue;
            }

            $rows[] = [
                'range_from'           => $tier['range_from'],
                'range_to'             => $tier['range_to'],
                'rate_per_kw'          => $tier['rate_per_kw'],
                'consumption_in_range' => round($rangeLimit, 2),
                'subtotal'             => round($rangeLimit * $tier['rate_per_kw'], 2),
            ];

            $remaining -= $rangeLimit;
        }

        return $rows;
    }
}