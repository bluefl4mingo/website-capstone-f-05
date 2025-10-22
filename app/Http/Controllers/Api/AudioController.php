<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Audio;
use Illuminate\Http\Request;
use App\Http\Resources\AudioResource;

class AudioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return AudioResource::collection(
        Audio::with('category')->latest()->paginate(50)
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Audio $audio)
    {
        return new AudioResource($audio->load('category'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Audio $audio)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Audio $audio)
    {
        //
    }

    public function download(Audio $audio)
    {
    $this->authorize('view', $audio);

    $url = Storage::disk('s3')->temporaryUrl(
        $audio->storage_path, now()->addMinutes(15)
    );

    return response()->json(['url' => $url]);
    }
}
