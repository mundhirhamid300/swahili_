@props(['headers' => [], 'empty' => 'No records found.', 'emptyColspan' => 1])

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                @if(count($headers))
                <thead class="thead-light">
                    <tr>
                        @foreach($headers as $header)
                            <th>{{ $header }}</th>
                        @endforeach
                    </tr>
                </thead>
                @endif
                <tbody>
                    {{ $slot }}
                </tbody>
            </table>
        </div>
    </div>
</div>
