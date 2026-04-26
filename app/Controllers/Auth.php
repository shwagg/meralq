<?php

namespace App\Controllers;

use App\Models\UsersModel;
use CodeIgniter\HTTP\ResponseInterface;

class Auth extends BaseController
{
    public function index()
    {
        $redirect = $this->redirectAuthenticatedUser();

        if ($redirect !== null) {
            return $redirect;
        }

        return view('auth/login', [
            'pageTitle' => 'Sign In',
        ]);
    }

    public function attemptLogin(): ResponseInterface
    {
        $credential = trim((string) $this->request->getPost('credential'));
        $password   = (string) $this->request->getPost('password');

        if ($credential === '' || $password === '') {
            return $this->response->setStatusCode(422)->setJSON([
                'status'  => 'error',
                'message' => 'Username or email and password are required.',
            ]);
        }

        $usersModel = new UsersModel();
        $user       = $usersModel->findByCredential($credential);

        if ($user === null || ! password_verify($password, $user['password'])) {
            return $this->response->setStatusCode(401)->setJSON([
                'status'  => 'error',
                'message' => 'Invalid login credentials.',
            ]);
        }

        $this->session->set([
            'userId'     => (int) $user['id'],
            'fullname'   => $user['fullname'],
            'username'   => $user['username'],
            'email'      => $user['email'],
            'role'       => $user['role'],
            'isLoggedIn' => true,
        ]);

        return $this->response->setJSON([
            'status'   => 'success',
            'message'  => 'Login successful.',
            'redirect' => $user['role'] === 'admin' ? site_url('dashboard/admin') : site_url('dashboard/user'),
        ]);
    }

    public function logout()
    {
        $this->session->destroy();

        return redirect()->to('/login');
    }
}