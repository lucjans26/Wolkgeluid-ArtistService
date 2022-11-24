<?php

namespace App\Http\Controllers;

use App\Classes\Responses\ResponseStrings;
use App\Models\Album;
use App\Models\Artist;
use App\Traits\AuthTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Classes\Responses\InvalidResponse;
use App\Classes\Responses\ValidResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class AlbumController extends Controller
{
    const ABILITY = 'album';
    //Create - post
    public function createAlbum(Request $request)
    {
        $validatedata = $request->validate([
            'name' => 'required|string|max:255|unique:album,name',
            'bio' => 'required|string|max:1500',
            'artist_id' => 'required|integer',
            'pat' => 'required|string',
            //'thumbnail' => 'image|max:1999'
        ]);

        if (AuthTrait::checkAbility(self::ABILITY, $validatedata['pat']))
        {
            $user_id = AuthTrait::checkToken($validatedata['pat']);
            $artist = Artist::where('id', $validatedata['artist_id'])->where('user_id', $user_id)->first();
            if ($artist)
            {
                $album = new Album([
                    'name' => $validatedata['name'],
                    'bio' => $validatedata['bio'],
                    'artist_id' => $validatedata['artist_id'],
                    'releaseDate' => Carbon::now()->toDateString(),
                    //'thumbnail' => $validatedata['thumbnail'] Needs saving and referencing to location
                ]);
                $album->save();

                if ($album)
                {
                    $response = new ValidResponse($album);
                    return response()->json($response, 200);
                }
            }
            $response = new InvalidResponse(ResponseStrings::NOT_FOUND);
            return response()->json($response, 404);
        }
        $response = new InvalidResponse(ResponseStrings::UNAUTHORIZED);
        return response()->json($response, 401);
    }

    //Read - get/id
    public function getAlbum(Request $request)
    {
        $validateData = $request->validate([
            'albumId' => '',
            'pat' => 'required|string'
        ]);

        if (AuthTrait::checkAbility(self::ABILITY, $validateData['pat']))
        {
            if (key_exists('albumId', $validateData))
            {
                $album = album::where('id', $validateData['albumId'])->get();
                if ($album)
                {
                    return response()->json($album, 200);
                }
                $response = new InvalidResponse(ResponseStrings::NOT_FOUND);
                return response()->json($response, 404);
            }
            else
            {
                $albums = Album::all();
                return response()->json($albums, 200);
            }
        }
        $response = new InvalidResponse(ResponseStrings::UNAUTHORIZED);
        return response()->json($response, 401);
    }

    //Update - put
    public function updateAlbum(Request $request)
    {
        $validateData = $request->validate([
            'name' => 'max:255',
            'bio' => 'max:1500',
            'album_id' => 'required|integer',
            'thumbnail' => 'image|max:1999',
            'pat' => 'required|string',
        ]);

        if(authTrait::checkAbility(self::ABILITY, $validateData['pat']))
        {
            $user_id = AuthTrait::checkToken($validateData['pat']);
            $album = Album::where('id', $validateData['album_id'])->first();
            $artist = Artist::where('id', $album['artist_id'])->where('user_id', $user_id)->first();
            if ($album && $artist)
            {
                $artist->update($validateData);
                $artist->save();
                return response()->json($artist, 200);
            }
            $response = new InvalidResponse(ResponseStrings::NOT_FOUND);
            return response()->json($response, 404);
        }
        $response = new InvalidResponse(ResponseStrings::UNAUTHORIZED);
        return response()->json($response, 401);
    }

    //Delete - delete
    public function deleteAlbum(Request $request)
    {
        $validateData = $request->validate([
            'album_id' => 'required|integer',
            'pat' => 'required|string'
        ]);

        if(AuthTrait::checkAbility(self::ABILITY, $validateData['pat']))
        {
            $user_id = AuthTrait::checkToken($validateData['pat']);
            $album = Album::where('id', $validateData['album_id'])->first();
            $artist = Artist::where('id', $album['artist_id'])->where('user_id', $user_id)->first();
            if ($album && $artist)
            {
                $album->delete();
                $response = new ValidResponse(ResponseStrings::DELETED);
                return response()->json($response, 200);
            }
            $response = new InvalidResponse(ResponseStrings::NOT_FOUND);
            return response()->json($response, 404);
        }
        $response = new InvalidResponse(ResponseStrings::UNAUTHORIZED);
        return response()->json($response, 401);
    }
}
