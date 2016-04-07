<?php

use App\User;
use Symfony\Component\HttpFoundation\Response;

class UserControllerTest extends TestCase
{
    /** @before */
    public function runDatabaseMigrations()
    {
        $this->artisan('migrate');
    }

    /** @test */
    public function it_should_create_a_new_user()
    {
        $params = [
            'email'                 => 'test@test.com',
            'password'              => 'password',
            'password_confirmation' => 'password',
        ];

        $this->post('/user', $params)
                ->seeJsonStructure([
                     'data',
                 ]);

        $this->assertResponseStatus(Response::HTTP_CREATED);
        $this->seeInDatabase('users', [
            'email' => array_get($params, 'email'),
        ]);
    }

    /** @test */
    public function it_should_faild_validation_with_same_email_users_registration()
    {
        $params = [
            'email'                 => 'test@test.com',
            'password'              => 'password',
            'password_confirmation' => 'password',
        ];

        $this->post('/user', $params);
        $this->post('/user', $params);

        $this->assertResponseStatus(Response::HTTP_BAD_REQUEST);
        $this->assertEquals(1, User::where('email', array_get($params, 'email'))->count());
    }

    /** @test */
    public function it_should_faild_validation_with_different_password_confirmation()
    {
        $params = [
            'email'                 => 'test@test.com',
            'password'              => 'password',
            'password_confirmation' => 'test',
        ];

        $this->post('/user', $params);

        $this->assertResponseStatus(Response::HTTP_BAD_REQUEST);
        $this->assertEquals(0, User::where('email', array_get($params, 'email'))->count());
    }

    /** @test */
    public function it_should_show_a_user_in_database()
    {
        $user = $this->createUser();

        $this->get("/user/{$user->id}")
                ->seeJsonStructure([
                     'data',
                 ]);

        $body = json_decode($this->response->getContent());

        $this->assertResponseStatus(Response::HTTP_OK);
        $this->assertEquals($user->id, $body->data->id);
    }

    /** @test */
    public function it_should_return_not_found_if_user_doesent_exist()
    {
        $this->get("/user/5")
                ->seeJsonStructure([
                     'status',
                     'message'
                 ]);

        $this->assertResponseStatus(Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function it_should_update_email_of_an_existing_user()
    {
        $user = $this->createUser();

        $params = [
            'email' => 'test@test.com',
        ];

        $this->put("/user/{$user->id}", $params)
                ->seeJsonStructure([
                     'data',
                 ]);

        $this->assertResponseStatus(Response::HTTP_OK);
        $this->seeInDatabase('users', [
            'email' => array_get($params, 'email'),
        ]);
    }

    /** @test */
    public function it_should_update_password_of_an_existing_user()
    {
        $user     = $this->createUser();
        $password = 'password';

        $params = [
            'password'              => $password,
            'password_confirmation' => $password,
        ];

        $this->put("/user/{$user->id}", $params)
                ->seeJsonStructure([
                     'data',
                 ]);

        $user = User::where('email', $user->email)->first();

        $this->assertResponseStatus(Response::HTTP_OK);
        $this->assertTrue(app('hash')->check($password, $user->password));
    }

    /** @test */
    public function it_should_faild_update_validation_if_empty_email()
    {
        $user = $this->createUser();

        $params = [
            'email' => '',
        ];

        $this->put("/user/{$user->id}", $params)
                ->seeJsonStructure([
                     'status',
                     'message'
                 ]);

        $this->assertResponseStatus(Response::HTTP_BAD_REQUEST);
    }

    /** @test */
    public function it_should_faild_update_validation_if_password_email()
    {
        $user = $this->createUser();

        $params = [
            'password'              => '',
            'password_confirmation' => ''
        ];

        $this->put("/user/{$user->id}", $params)
                ->seeJsonStructure([
                     'status',
                     'message'
                 ]);

        $this->assertResponseStatus(Response::HTTP_BAD_REQUEST);
    }

    /** @test */
    public function it_should_destroy_a_user()
    {
        $user = $this->createUser();

        $this->delete("/user/{$user->id}")
                ->seeJsonStructure([
                     'data',
                 ]);

        $body  = json_decode($this->response->getContent());
        $query = User::where('email', $body->data->email);

        $this->assertEquals(0, $query->count());
    }

    /** @test */
    public function it_should_not_destroy_a_user_not_in_database()
    {
        $this->delete("/user/1")
                ->seeJsonStructure([
                     'status',
                     'message'
                 ]);

        $this->assertResponseStatus(Response::HTTP_NOT_FOUND);
    }
}
