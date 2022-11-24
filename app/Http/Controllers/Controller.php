<?php

namespace App\Http\Controllers;

use App\Classes\Responses\InvalidResponse;
use App\Classes\Responses\ResponseStrings;
use App\Classes\Responses\ValidResponse;
use App\Models\Artist;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class ArtistController extends Controller
{
    //Create - post
    public function createArtist(Request $request)
    {
        $validateData = $request->validate([
            'artistName' => 'required|string|max:100|unique:artist,name',
            'bio' => 'required|string|max:1500',
        ]);


        $user = Auth::user();
        $artist = new Artist(['name' => $validateData['artistName'], 'bio' => $validateData['bio']]);
        $user->artists()->save($artist);
        $response = new ValidResponse($artist);
        return response()->json($response, 200);
    }


    //Update - put
    public function deleteArtist(Request $request)
    {
        $validateData = $request->validate([
            'artistId' => 'required'
        ]);

        $user = Auth::user();
        $artist = Artist::where('id', '=', $validateData['artistId'])->first();
        if ($artist)
        {
            if ($artist['user_id'] == $user['id'])
            {
                $artist->delete();
                $response = new ValidResponse(ResponseStrings::DELETED);
                return response()->json($response, 200);
            }
            $response = new InvalidResponse("unauthorized");
            return response()->json($response, 401);
        }
        $response = new InvalidResponse(ResponseStrings::NOT_FOUND);
        return response()->json($response, 404);
    }

    //Delete - delete
    public function updateArtist(Request $request)
    {
        $validateData = $request->validate([
            'artistName' => 'required|string|max:100',
            'artistId' => 'required',
            'bio' => 'required|string|max:1500',
        ]);

        $user = Auth::user();
        $artist = Artist::where('id', $validateData['artistId'])->where('user_id', '=', $user['id'])->first();
        if ($artist)
        {
            $artist->update(['name' => $validateData['artistName'], 'bio' => $validateData['bio']]);
            return response()->json($artist, 200);
        }
        $response = new InvalidResponse(ResponseStrings::NOT_FOUND);
        return response()->json($response, 404);
    }

    //Read - get/id
    public function getArtist(Request $request)
    {
        $validateData = $request->validate([
            'artistId' => '',
        ]);

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
}
