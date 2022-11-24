<?php

namespace App\Http\Controllers;

use App\Classes\Responses\InvalidResponse;
use App\Classes\Responses\ResponseStrings;
use App\Classes\Responses\ValidResponse;
use App\Models\Artist;
use App\Traits\AuthTrait;
use App\Traits\MessageTrait;
use App\Traits\Topics;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class ArtistController extends Controller
{
    const ABILITY = 'artist';

    //Create - post
    public function createArtist(Request $request)
    {
        $validateData = $request->validate([
            'artistName' => 'required|string|max:100|unique:artist,name',
            'bio' => 'required|string|max:1500',
            'pat' => 'required|string'
        ]);

        if(AuthTrait::checkAbility(self::ABILITY, $validateData['pat']))
        {
            $user_id = AuthTrait::checkToken($validateData['pat']);
            $artist = new Artist(['name' => $validateData['artistName'], 'bio' => $validateData['bio'], 'user_id' => $user_id]);
            $artist->save();
            if ($artist)
            {
                $response = new ValidResponse($artist);
                return response()->json($response, 200);
            }
            $response = new InvalidResponse(ResponseStrings::INTERNAL_ERROR);
            return response()->json($response, 500);
        }
        $response = new InvalidResponse(ResponseStrings::UNAUTHORIZED);
        return response()->json($response, 401);
    }


    //Update - put
    public function deleteArtist(Request $request)
    {
        $validateData = $request->validate([
            'artistId' => 'required',
            'pat' => 'required|string'
        ]);

        if (AuthTrait::checkAbility(self::ABILITY, $validateData['pat']))
        {
            $user_id = AuthTrait::checkToken($validateData['pat']);
            $artist = Artist::where('id', '=', $validateData['artistId'])->where('user_id', '=', $user_id)->first();
            if ($artist)
            {
                if ($validateData['userId'] == $artist->user_id)
                {
                    $artist->delete();
                    //Make sure to delete all the albums and songs associated with the artist through artist topic RabbitMQ
                    MessageTrait::publish(Topics::ARTIST, json_encode(['action' => 'delete', 'artistId' => $artist['id']]));
                    $response = new ValidResponse(ResponseStrings::DELETED);
                    return response()->json($response, 200);
                }
                $response = new InvalidResponse("unauthorized");
                return response()->json($response, 401);
            }
            $response = new InvalidResponse(ResponseStrings::NOT_FOUND);
            return response()->json($response, 404);
        }
        $response = new InvalidResponse(ResponseStrings::UNAUTHORIZED);
        return response()->json($response, 401);
    }

    //Delete - delete
    public function updateArtist(Request $request)
    {
        $validateData = $request->validate([
            'artistName' => 'required|string|max:100',
            'artistId' => 'required',
            'pat' => 'required|string',
            'bio' => 'required|string|max:1500',
        ]);

        if(AuthTrait::checkAbility(self::ABILITY, $validateData['pat']))
        {
            $user_id = AuthTrait::checkToken($validateData['pat']);
            $artist = Artist::where('id', $validateData['artistId'])->where('user_id', '=', $user_id)->first();
            if ($artist)
            {
                $artist->update(['name' => $validateData['artistName'], 'bio' => $validateData['bio']]);
                return response()->json($artist, 200);
            }
            $response = new InvalidResponse(ResponseStrings::NOT_FOUND);
            return response()->json($response, 404);
        }
        $response = new InvalidResponse(ResponseStrings::UNAUTHORIZED);
        return response()->json($response, 401);
    }

    //Read - get/id
    public function getArtist(Request $request)
    {
        $validateData = $request->validate([
            'artistId' => '',
            'pat' => 'required|string'
        ]);

        if (AuthTrait::checkAbility(self::ABILITY, $validateData['pat']))
        {
            if (key_exists('artistId', $validateData))
            {
                $artist = Artist::where('id', $validateData['artistId'])->get();
                if ($artist)
                {
                    return response()->json($artist, 200);
                }
                $response = new InvalidResponse(ResponseStrings::NOT_FOUND);
                return response()->json($response, 404);
            } else
            {
                $artists = Artist::all();
                return response()->json($artists, 200);
            }
        }
        $response = new InvalidResponse(ResponseStrings::UNAUTHORIZED);
        return response()->json($response, 401);
    }
}
