<?php

namespace Tests\Feature;

use App\Traits\AuthTrait;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Sanctum\PersonalAccessToken;
use Tests\TestCase;
use function PHPUnit\Framework\assertEmpty;
use function PHPUnit\Framework\assertEquals;

class AuthTest extends TestCase
{
    use DatabaseTransactions;
    use DatabaseMigrations;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_auth_token_registration()
    {
        //Arrange
        $setupToken = ['token' => 'test', 'user_id' => 1, 'abilities' => ['test']];

        //Act
        AuthTrait::registerToken($setupToken);

        //Assert
        $databaseToken = PersonalAccessToken::where('token', $setupToken['token'])->first();
        $resultToken = ['token' => $databaseToken->token, 'user_id' => $databaseToken->user_id, 'abilities' => $databaseToken->abilities];
        assertEquals($setupToken, $resultToken);
    }

    public function test_auth_exising_token_check()
    {
        //Arrange
        $setupToken = ['token' => 'test', 'user_id' => 1, 'abilities' => ['test']];
        AuthTrait::registerToken($setupToken);

        //Act
        $user_id = AuthTrait::checkToken('test');
        //Assert
        assertEquals(1, $user_id);

    }

    public function test_auth_non_exising_token_check()
    {
        //Arrange
        $setupToken = ['token' => 'test', 'user_id' => 1, 'abilities' => ['test']];
        AuthTrait::registerToken($setupToken);

        //Act
        $user_id = AuthTrait::checkToken('fakeToken');

        //Assert
        assertEquals(false, $user_id);

    }


    public function test_auth_token_deletion()
    {
        //Arrange
        $setupToken = ['token' => 'test', 'user_id' => 1, 'abilities' => ['test']];
        AuthTrait::registerToken($setupToken);

        //Act
        AuthTrait::deleteTokens(1);

        //Assert
        assertEmpty(PersonalAccessToken::where('user_id', 1)->get());
    }
}
