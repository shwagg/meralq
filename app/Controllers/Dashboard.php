<?php

namespace App\Controllers;

use App\Models\AuditLogsModel;
use App\Models\UsersModel;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;

class Dashboard extends BaseController
{
    private UsersModel $usersModel;

    private AuditLogsModel $auditLogsModel;

    public function __construct()
    {
        $this->usersModel     = new UsersModel();
        $this->auditLogsModel = new AuditLogsModel();
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

    public function user(): string|RedirectResponse
    {
        $user = $this->requireRole('user');

        if ($user instanceof RedirectResponse) {
            return $user;
        }

        return view('dashboard/user', [
            'pageTitle' => 'User Dashboard',
            'user'      => $user,
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

    private function dashboardPayload(): array
    {
        return [
            'users'     => $this->usersModel->listForAdmin(),
            'auditLogs' => $this->auditLogsModel->latestWithUsers(),
        ];
    }
}