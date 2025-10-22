<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Audio;
use Illuminate\Http\Request;

class AudioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAudioRequest $request)
    {
    $this->authorize('create', Audio::class);

    $file = $request->file('file');
    $path = Storage::disk('s3')->putFile('audios', $file);  // simpan ke S3

    $audio = Audio::create([
        'title'        => $request->string('title'),
        'description'  => $request->string('description'),
        'category_id'  => $request->input('category_id'),
        'storage_path' => $path,
        'mime_type'    => $file->getMimeType(),
        'size_bytes'   => $file->getSize(),
        // 'duration_sec' => ... (opsional, jika ekstrak durasi)
    ]);

    return redirect()->route('audios.index')->with('status', 'Audio uploaded.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Audio $audio)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Audio $audio)
    {
        //
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
        $this->authorize('delete', $audio);

        // hapus file di S3
        Storage::disk('s3')->delete($audio->storage_path);

        $audio->delete();

        return back()->with('status', 'Audio deleted.');
    }
}
