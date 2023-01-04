<?php

namespace Tests\Feature;

use App\Models\Artist;
use App\Traits\AuthTrait;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AlbumTest extends TestCase
{
    use DatabaseTransactions;
    use DatabaseMigrations;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    //GET artists
    public function test_get__all_artists_valid_token()
    {
        $setupToken = ['token' => 'test', 'user_id' => 1, 'abilities' => ['artist']];
        AuthTrait::registerToken($setupToken);

        $response = $this->get('/api/artists?pat=test');
        $response->assertStatus(200);
    }

    public function test_get_all_artist_invalid_token()
    {
        $setupToken = ['token' => 'test', 'user_id' => 1, 'abilities' => ['artist']];
        AuthTrait::registerToken($setupToken);

        $response = $this->get('/api/artists?pat=fakeKey');
        $response->assertStatus(401);
    }

    public function test_get_fake_artist_invalid_token()
    {
        $setupToken = ['token' => 'test', 'user_id' => 1, 'abilities' => ['artist']];
        AuthTrait::registerToken($setupToken);

        $response = $this->get('/api/artists?pat=fakeKey&artistId=1');
        $response->assertStatus(401);
    }

    public function test_get_fake_artist_valid_token()
    {
    $setupToken = ['token' => 'test', 'user_id' => 1, 'abilities' => ['artist']];
    AuthTrait::registerToken($setupToken);

    $response = $this->get('/api/artists?pat=test&artistId=1234567890');
    $response->assertStatus(404);
    }

    public function test_get_artist_invalid_token()
    {
        $setupToken = ['token' => 'test', 'user_id' => 1, 'abilities' => ['artist']];
        $artist = new Artist(['name' => "fake", 'bio' => "test", 'user_id' => 1]);
        $artist->save();
        AuthTrait::registerToken($setupToken);

        $response = $this->get('/api/artists?pat=fakeKey&artistId=1');
        $response->assertStatus(401);
    }

    public function test_get_artist_valid_token()
    {
        $setupToken = ['token' => 'test', 'user_id' => 1, 'abilities' => ['artist']];
        $artist = new Artist(['name' => "fake", 'bio' => "test", 'user_id' => 1]);
        $artist->save();
        AuthTrait::registerToken($setupToken);

        $response = $this->get('/api/artists?pat=test&artistId=1');
        $response->assertStatus(200);
    }

    //POST artists
    public function test_create_valid_artist_valid_token()
    {
        $setupToken = ['token' => 'test', 'user_id' => 1, 'abilities' => ['artist']];
        AuthTrait::registerToken($setupToken);
        $setup_artist = new Artist(['name' => "fake", 'bio' => "test", 'user_id' => 1]);
        $response = $this->post('/api/artists?pat=test', ['artistName' => $setup_artist->name, 'bio' => $setup_artist->bio, 'pat' => 'test']);
        $response->assertStatus(200);
    }

    public function test_create_invalid_artist_valid_token()
    {
        $setupToken = ['token' => 'test', 'user_id' => 1, 'abilities' => ['artist']];
        AuthTrait::registerToken($setupToken);
        $setup_artist = new Artist(['name' => "fake", 'bio' => "test", 'user_id' => 1]);
        $response = $this->post('/api/artists', ['artistName' => $setup_artist->name, 'pat' => 'test'], ['accept' => 'application/json']);
        $response->assertStatus(422);
    }

    public function test_create_valid_artist_invalid_token()
    {
        $setup_artist = new Artist(['name' => "fake", 'bio' => "test", 'user_id' => 1]);
        $response = $this->post('/api/artists?pat=test', ['artistName' => $setup_artist->name, 'bio' => $setup_artist->bio, 'pat' => 'test']);
        $response->assertStatus(401);
    }

    public function test_create_invalid_artist_invalid_token()
    {
        $setup_artist = new Artist(['name' => "fake", 'bio' => "test", 'user_id' => 1]);
        $response = $this->post('/api/artists', ['artistName' => $setup_artist->name, 'pat' => 'test'], ['accept' => 'application/json']);
        $response->assertStatus(422);
    }

    public function test_update_valid_artist_valid_token()
    {
        $setupToken = ['token' => 'test', 'user_id' => 1, 'abilities' => ['artist']];
        AuthTrait::registerToken($setupToken);
        $setup_artist = new Artist(['name' => "fake", 'bio' => "test", 'user_id' => 1]);
        $setup_artist->save();

        $response = $this->put('/api/artists', ['artistName' => $setup_artist->name, 'bio'=>"hang me", 'artistId' =>1, 'pat' => 'test']);
        $response->assertStatus(200);
    }

    public function test_update_valid_artist_invalid_token()
    {
        $setup_artist = new Artist(['name' => "fake", 'bio' => "test", 'user_id' => 1]);
        $setup_artist->save();

        $response = $this->put('/api/artists', ['artistName' => $setup_artist->name, 'bio'=>"hang me", 'artistId' =>1, 'pat' => 'test']);
        $response->assertStatus(401);
    }

    public function test_update_invalid_artist_valid_token()
    {
        $setup_artist = new Artist(['name' => "fake", 'bio' => "test", 'user_id' => 1]);
        $setup_artist->save();

        $response = $this->put('/api/artists', ['artistName' => $setup_artist->name, 'bio'=>"hang me", 'pat' => 'test'], ['accept' => 'application/json']);
        $response->assertStatus(422);
    }

    public function test_delete_invalid_artist_valid_token()
    {
        $setupToken = ['token' => 'test', 'user_id' => 1, 'abilities' => ['artist']];
        AuthTrait::registerToken($setupToken);

        $setup_artist = new Artist(['name' => "fake", 'bio' => "test", 'user_id' => 1]);
        $setup_artist->save();

        $response = $this->delete('/api/artists', ['artistId' => 2, 'pat' => 'test'], ['accept' => 'application/json']);
        $response->assertStatus(404);
    }

    public function test_delete_valid_artist_invalid_token()
    {
        $setup_artist = new Artist(['name' => "fake", 'bio' => "test", 'user_id' => 1]);
        $setup_artist->save();

        $response = $this->delete('/api/artists', ['artistId' => 2, 'pat' => 'test'], ['accept' => 'application/json']);
        $response->assertStatus(401);
    }

}
