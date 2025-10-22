@extends('layouts.app')

@section('content')
<div class="container">
  <h1 class="mb-4">Audio Files</h1>

  <a href="{{ route('audios.create') }}" class="btn btn-primary mb-3">Upload Audio</a>

  @if (session('status'))
  <div class="alert alert-success">{{ session('status') }}</div>
    @endif
  @error('file')
    <div class="alert alert-danger">{{ $message }}</div>
  @enderror


  <table class="table table-striped">
    <thead>
      <tr>
        <th>Title</th><th>Category</th><th>Size</th><th>Uploaded</th><th></th>
      </tr>
    </thead>
    <tbody>
      @foreach ($audios as $audio)
        <tr>
          <td>{{ $audio->title }}</td>
          <td>{{ optional($audio->category)->name ?? '-' }}</td>
          <td>{{ number_format(($audio->size_bytes ?? 0)/1024, 0) }} KB</td>
          <td>{{ $audio->created_at->format('Y-m-d H:i') }}</td>
          <td class="text-end">
            <a href="{{ route('audios.edit', $audio) }}" class="btn btn-sm btn-secondary">Edit</a>
            <form action="{{ route('audios.destroy', $audio) }}" method="POST" class="d-inline"
                  onsubmit="return confirm('Yakin hapus? Tindakan ini tidak bisa dibatalkan.');">
              @csrf @method('DELETE')
              <button class="btn btn-sm btn-danger" type="submit">Delete</button>
            </form>
          </td>
        </tr>
      @endforeach
    </tbody>
  </table>

  {{ $audios->links() }}
</div>
@endsection
