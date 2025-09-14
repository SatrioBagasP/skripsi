@if ($logs->isEmpty())
    <div class="">Data tidak ditemukan. Periksa inputan anda kembali</div>
@else
    <ul class="tracking position-relative">
        @foreach ($logs as $log)
            <li class="{{ $loop->first ? 'current-step' : '' }}">
                <div class="dot {{ $loop->first ? 'done' : 'pending' }}">{{ $logs->count() - $loop->index }}</div>
                <div class="title">{{ ucfirst($log->description) }}</div>
                <div class="time">{{ $log->created_at->format('d M Y H:i') }}</div>
            </li>
        @endforeach
    </ul>
@endif
