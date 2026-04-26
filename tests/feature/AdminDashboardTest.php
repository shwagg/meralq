<?php

declare(strict_types=1);

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

final class AdminDashboardTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $seed = 'Tests\\Support\\Database\\Seeds\\AdminDashboardSeeder';

    public static function setUpBeforeClass(): void
    {
        putenv('database.tests.DBDriver=MySQLi');
        putenv('database.tests.database=meralkoo_db');
        putenv('database.tests.hostname=localhost');
        putenv('database.tests.username=root');
        putenv('database.tests.password=');
        putenv('database.tests.DBPrefix=ci_test_');

        $_ENV['database.tests.DBDriver'] = 'MySQLi';
        $_ENV['database.tests.database'] = 'meralkoo_db';
        $_ENV['database.tests.hostname'] = 'localhost';
        $_ENV['database.tests.username'] = 'root';
        $_ENV['database.tests.password'] = '';
        $_ENV['database.tests.DBPrefix'] = 'ci_test_';

        $_SERVER['database.tests.DBDriver'] = 'MySQLi';
        $_SERVER['database.tests.database'] = 'meralkoo_db';
        $_SERVER['database.tests.hostname'] = 'localhost';
        $_SERVER['database.tests.username'] = 'root';
        $_SERVER['database.tests.password'] = '';
        $_SERVER['database.tests.DBPrefix'] = 'ci_test_';

        parent::setUpBeforeClass();
    }

    public function testAdminDashboardPageLoadsForAdminSession(): void
    {
        $result = $this->withSession([
            'userId'     => 1,
            'fullname'   => 'System Administrator',
            'username'   => 'admin',
            'email'      => 'admin@test.com',
            'role'       => 'admin',
            'isLoggedIn' => true,
        ])->get('/dashboard/admin');

        $result->assertOK();
        $result->assertSee('Manage accounts and monitor platform activity.');
        $result->assertSee('Registered Users');
        $result->assertSee('Audit Trail');
    }

    public function testAdminCanCreateUserAndAuditEntryIsRecorded(): void
    {
        $result = $this->withSession([
            'userId'     => 1,
            'fullname'   => 'System Administrator',
            'username'   => 'admin',
            'email'      => 'admin@test.com',
            'role'       => 'admin',
            'isLoggedIn' => true,
        ])->call('POST', '/dashboard/admin/users', [
            'fullname' => 'New Staff Member',
            'username' => 'staff1',
            'email'    => 'staff1@test.com',
            'password' => 'secret123',
            'role'     => 'user',
        ]);

        $result->assertStatus(201);

        $payload = json_decode($result->getJSON(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame('success', $payload['status']);
        $this->assertSame('User account created.', $payload['message']);
        $this->assertCount(3, $payload['data']['users']);

        $this->seeInDatabase('users', [
            'username' => 'staff1',
            'email'    => 'staff1@test.com',
            'role'     => 'user',
        ]);

        $this->seeInDatabase('audit_logs', [
            'userId' => 1,
            'action' => 'create_user',
        ]);
    }

    public function testAdminAuditTrailPageLoadsForAdminSession(): void
    {
        $result = $this->withSession([
            'userId'     => 1,
            'fullname'   => 'System Administrator',
            'username'   => 'admin',
            'email'      => 'admin@test.com',
            'role'       => 'admin',
            'isLoggedIn' => true,
        ])->get('/dashboard/admin/audit-trail');

        $result->assertOK();
        $result->assertSee('Browse audit activity without stretching the main dashboard.');
        $result->assertSee('Page 1 of 1. 2 total entries.');
    }

    public function testUserCanComputeBillAndPersistBreakdown(): void
    {
        $result = $this->withSession([
            'userId'     => 2,
            'fullname'   => 'Regular User',
            'username'   => 'user1',
            'email'      => 'user1@test.com',
            'role'       => 'user',
            'isLoggedIn' => true,
        ])->call('POST', '/dashboard/user/bills', [
            'client_name'    => 'Metered Home',
            'client_address' => '456 Current Avenue',
            'kw_consumption' => '550',
        ]);

        $result->assertStatus(201);

        $payload = json_decode($result->getJSON(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame('success', $payload['status']);
        $this->assertSame('Electric bill computed successfully.', $payload['message']);
        $this->assertSame(2, $payload['data']['metrics']['billsCount']);

        $this->seeInDatabase('clients', [
            'name'      => 'Metered Home',
            'createdBy' => 2,
        ]);

        $this->seeInDatabase('bills', [
            'userId'         => 2,
            'kw_consumption' => 550.00,
            'total_amount'   => 6650.00,
        ]);

        $this->seeInDatabase('bill_breakdown', [
            'range_from'           => 501,
            'rate_per_kw'          => 15.00,
            'consumption_in_range' => 50.00,
            'subtotal'             => 750.00,
        ]);

        $this->seeInDatabase('audit_logs', [
            'userId' => 2,
            'action' => 'compute_bill',
        ]);
    }

    public function testUserAuditTrailPageLoadsForUserSession(): void
    {
        $result = $this->withSession([
            'userId'     => 2,
            'fullname'   => 'Regular User',
            'username'   => 'user1',
            'email'      => 'user1@test.com',
            'role'       => 'user',
            'isLoggedIn' => true,
        ])->get('/dashboard/user/audit-trail');

        $result->assertOK();
        $result->assertSee('Review your own billing activity on a dedicated page.');
        $result->assertSee('Page 1 of 1. 1 total entries.');
    }
}