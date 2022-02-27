<?php

namespace Tests\Repositories;

use Tests\TestCase;
use App\Repositories\StudentRepository;
use App\Services\AccountService;

class StudentRepositoryTest extends TestCase
{

    protected $studentRepository;

    public function setUp(): void
    {
        parent::setUp();
        $this->studentRepository = new StudentRepository();
        $this->accountService = $this->app->make('App\Services\AccountService');
    }

    /**
     * @test
     */
    public function testStudentDoLogin()
    {
        $email = 'ldelima@zoho.com';
        $pass = '1234';
        $resp = $this->studentRepository->doLogin($email, $pass);
        $this->assertFalse($resp["ok"]);
        $pass = '12345678';
        $resp = $this->studentRepository->doLogin($email, $pass);
        $this->assertTrue($resp["ok"]);
    }

    /**
     * @test
     */
    public function testStudentActivateUser()
    {
        $fakeToken = '12345';
        $result = $this->studentRepository->isTokenActive($fakeToken);
        $this->assertFalse($result);
    }

    /**
     * @test
     */
    public function testGetStudentById()
    {
        $studentId = 0;
        $result = $this->studentRepository->getStudentById($studentId);
        $this->assertNull($result);

        $studentId = 490;
        $result = collect($this->studentRepository->getStudentById($studentId));
        $this->assertIsObject($result);

        $this->assertTrue($result->has("id"));
        $this->assertFalse($result->has("token"));
        $this->assertFalse($result->has("foto"));
    }

    /**
     * @test
     */
    public function testGetUserContacts()
    {
        $fakeId = 0;
        $result = $this->accountService->getUserContacts($fakeId);
        $this->assertFalse($result);

        $userId = 476;
        $result = $this->accountService->getUserContacts($userId);
        $this->assertIsArray($result);

        if (count($result) > 0) {
            $contact = $result[0];
            $this->assertArrayHasKey("email", $contact);
        }
    }
}
