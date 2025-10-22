@extends('layouts.app')

@section('content')
<div class="container">
  <h1>Upload Audio</h1>

  <x-auth-validation-errors class="mb-4" :errors="$errors" />

  <form action="{{ route('audios.store') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <div class="mb-3">
      <label class="form-label">Judul</label>
      <input type="text" name="title" class="form-control" value="{{ old('title') }}" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Deskripsi</label>
      <textarea name="description" class="form-control">{{ old('description') }}</textarea>
    </div>

    <div class="mb-3">
      <label class="form-label">Kategori</label>
      <select name="category_id" class="form-select">
        <option value="">— Pilih —</option>
        @foreach($categories as $c)
          <option value="{{ $c->id }}" @selected(old('category_id')==$c->id)>{{ $c->name }}</option>
        @endforeach
      </select>
    </div>

    <div class="mb-3">
      <label class="form-label">File Audio</label>
      <input type="file" name="file" class="form-control" required accept="audio/*">
      <small class="text-muted">Format umum: MP3/WAV/MP4 (audio). </small>
    </div>

    <button class="btn btn-primary">Upload</button>
  </form>
</div>
@endsection
