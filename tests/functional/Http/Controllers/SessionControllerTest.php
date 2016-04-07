<?php

use App\User;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Response;

class SessionControllerTest extends TestCase
{
    /** @before */
    public function runDatabaseMigrations()
    {
        $this->artisan('migrate');
    }

    /** @test */
    public function it_should_create_a_new_session()
    {
        $user = $this->createUser([
            'password' => 'password',
        ]);

        $params = [
            'email'    => $user->email,
            'password' => 'password',
        ];

        $this->post('session/login', $params)
                ->seeJsonStructure([
                     'data',
                 ]);

        $body       = json_decode($this->response->getContent());
        $token      = $body->data->session_token;
        $redis_user = json_decode($this->redis()->get($token));

        $this->assertResponseStatus(Response::HTTP_CREATED);
        $this->assertEquals($user->id, $redis_user->id);
        $this->assertGreaterThan(1, $this->redis()->ttl($token));
    }

    /** @test */
    public function it_should_fails_with_wrong_password()
    {
        $user = $this->createUser([
            'password' => 'password',
        ]);

        $params = [
            'email'    => $user->email,
            'password' => 'wrong',
        ];

        $this->post('session/login', $params);

        $this->assertResponseStatus(Response::HTTP_FORBIDDEN);
    }

    /** @test */
    public function it_should_logout_a_valid_user()
    {
        $this->redis()->set('token', 'user');

        $params = [
            'session_token' => 'token',
        ];

        $this->delete('session/logout', $params);

        $this->assertResponseStatus(Response::HTTP_NO_CONTENT);
        $this->assertNull($this->redis()->get('token'));
    }
}
