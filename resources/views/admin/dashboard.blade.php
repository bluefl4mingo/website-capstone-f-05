@extends('layouts.admin')

@section('title','Dashboard')

@section('content')
  {{-- KPI cards --}}
  <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
    @php
      $kpis = [
        ['label' => 'Items',          'value' => 156,  'sub' => 'published', 'icon' => '⚱️'],
        ['label' => 'Audio Tracks',   'value' => 142,  'sub' => 'Bahasa Indonesia', 'icon' => '🎧'],
        ['label' => 'Devices Online', 'value' => 18,   'sub' => 'of 22 total', 'icon' => '📱'],
        ['label' => 'Plays (7d)',     'value' => 3492, 'sub' => 'visitors listening', 'icon' => '▶️'],
      ];
    @endphp
    @foreach ($kpis as $kpi)
      <div class="rounded-2xl bg-white ring-1 ring-black/5 p-5 flex items-center gap-4">
        <div class="text-2xl">{{ $kpi['icon'] }}</div>
        <div>
          <div class="text-2xl font-semibold">{{ number_format($kpi['value']) }}</div>
          <div class="text-sm text-gray-500">{{ $kpi['label'] }}</div>
          <div class="text-xs text-gray-400">{{ $kpi['sub'] }}</div>
        </div>
      </div>
    @endforeach
  </div>

  {{-- Charts & tables --}}
  <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mt-6">
    {{-- Line chart --}}
    <div class="xl:col-span-2 rounded-2xl bg-white ring-1 ring-black/5 p-5">
      <div class="flex items-center justify-between mb-3">
        <h2 class="font-semibold">Plays per Day (last 14 days)</h2>
        <span class="text-xs text-gray-500">dummy data</span>
      </div>
      <canvas id="playsChart" height="80"></canvas>
    </div>

    {{-- Top items table --}}
    <div class="rounded-2xl bg-white ring-1 ring-black/5 p-5">
      <h2 class="font-semibold mb-3">Most Played Items</h2>
      <table class="w-full text-sm">
        <thead class="text-gray-500">
          <tr><th class="text-left py-2">Item</th><th class="text-right">7d Plays</th></tr>
        </thead>
        <tbody class="divide-y">
          @foreach ([
            ['title'=>'Meriam VOC abad XIX','plays'=>412],
            ['title'=>'Diorama Perang Diponegoro','plays'=>366],
            ['title'=>'Patung Jenderal Sudirman','plays'=>305],
            ['title'=>'Peta Hindia Belanda 1900','plays'=>298],
          ] as $row)
            <tr>
              <td class="py-2">{{ $row['title'] }}</td>
              <td class="py-2 text-right font-medium">{{ $row['plays'] }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    {{-- Recent device activity --}}
    <div class="xl:col-span-2 rounded-2xl bg-white ring-1 ring-black/5 p-5">
      <h2 class="font-semibold mb-3">Recent Device Activity</h2>
      <ul class="text-sm divide-y">
        @foreach ([
          ['device'=>'Handset-021','event'=>'Synced 23 items','time'=>'2m ago'],
          ['device'=>'Kiosk-Lobby','event'=>'Online, battery 82%','time'=>'12m ago'],
          ['device'=>'Handset-014','event'=>'Updated app v1.3.2','time'=>'35m ago'],
          ['device'=>'Handset-009','event'=>'Low battery (15%)','time'=>'1h ago'],
        ] as $log)
          <li class="py-2 flex items-center justify-between">
            <span>{{ $log['device'] }} — {{ $log['event'] }}</span>
            <span class="text-gray-500">{{ $log['time'] }}</span>
          </li>
        @endforeach
      </ul>
    </div>

    {{-- Quick actions --}}
    <div class="rounded-2xl bg-white ring-1 ring-black/5 p-5">
      <h2 class="font-semibold mb-3">Quick Actions</h2>
      <div class="grid grid-cols-2 gap-3 text-sm">
        <a href="{{ route('admin.items.index') }}" class="rounded-xl bg-mint/50 px-3 py-2 text-center text-aqua hover:bg-mint/70">New Item</a>
        <a href="{{ route('admin.audio.index') }}" class="rounded-xl bg-mint/50 px-3 py-2 text-center text-aqua hover:bg-mint/70">Upload Audio</a>
      </div>
    </div>
  </div>

  {{-- Chart.js (simple, local) --}}
  @push('scripts')
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    const ctx = document.getElementById('playsChart').getContext('2d');
    const labels = [...Array(14).keys()].map(i => new Date(Date.now()- (13-i)*864e5)
                             .toLocaleDateString('id-ID',{day:'2-digit',month:'short'}));
    const data = [180,210,240,205,260,275,310,290,320,335,360,370,395,410];
    new Chart(ctx,{
      type:'line',
      data:{ labels, datasets:[{ label:'Plays', data, fill:false, tension:.35 }]},
      options:{ plugins:{ legend:{ display:false }}, scales:{ y:{ beginAtZero:true } } }
    });
  </script>
  @endpush
@endsection