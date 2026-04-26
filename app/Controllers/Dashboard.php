<?php

namespace App\Controllers;

use CodeIgniter\HTTP\RedirectResponse;

class Dashboard extends BaseController
{
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
}