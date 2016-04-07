<?php

use App\User;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Response;

class PasswordResetControllerTest extends TestCase
{
    /** @before */
    public function runDatabaseMigrations()
    {
        $this->artisan('migrate');
    }

    /** @test */
    public function it_should_create_a_reset_password_record()
    {
        $user = $this->createUser();

        $params = [
            'email' => $user->email,
        ];

        $this->post('/password/email', $params);

        $this->assertResponseStatus(Response::HTTP_NO_CONTENT);
        $this->seeInDatabase('password_resets', [
            'email' => $user->email,
        ]);
    }

    /** @test */
    public function it_should_require_email_field()
    {
        $params = [
            'email' => '',
        ];

        $this->post('/password/email', $params)
                ->seeJsonStructure([
                     'status',
                     'message'
                 ]);

        $this->assertResponseStatus(Response::HTTP_BAD_REQUEST);
    }

    /** @test */
    public function it_should_return_not_found_if_user_does_not_exist()
    {
        $params = [
            'email' => 'test@test.com',
        ];

        $this->post('/password/email', $params)
                ->seeJsonStructure([
                     'status',
                     'message'
                 ]);

        $this->assertResponseStatus(Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function it_should_change_password_of_user()
    {
        $user = $this->createUser();

        app('db')
        ->table('password_resets')
        ->insert([
            'email'      => $user->email,
            'token'      => 'token',
            'created_at' => Carbon::now(),
        ]);

        $params = [
            'email'                 => $user->email,
            'token'                 => 'token',
            'password'              => 'password',
            'password_confirmation' => 'password',
        ];

        $this->post('/password/reset', $params)
                ->seeJsonStructure([
                     'data',
                 ]);

        $user = User::where('email', $user->email)->first();

        $this->assertResponseStatus(Response::HTTP_OK);
        $this->assertTrue(app('hash')->check('password', $user->password));
    }

    /** @test */
    public function it_should_not_process_request_if_wrong_token()
    {
        $user = $this->createUser();

        $params = [
            'email'                 => $user->email,
            'token'                 => 'token',
            'password'              => 'password',
            'password_confirmation' => 'password',
        ];

        $this->post('/password/reset', $params)
                ->seeJsonStructure([
                     'status',
                     'message'
                 ]);

        $this->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}
