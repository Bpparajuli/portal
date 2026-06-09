@props([
    'title' => '',
    'headers' => [],
    'rows' => null,
    'rowComponent' => null,
    'routePrefix' => 'admin',
    'totalRequiredDocs' => 0,
    'emptyMessage' => 'No records found.',
    'pagination' => null,
])

<div class="card border-0 shadow-sm rounded-3">
    @if($title)
    <div class="card-header bg-white border-0 px-4 pt-4 pb-0">
        <h6 class="fw-bold mb-0 small text-uppercase text-muted">{{ $title }}</h6>
    </div>
    @endif
    <div class="table-responsive">
        <table class="table table-sm table-hover align-middle mb-0">
            @if(count($headers) > 0)
            <thead class="table-light">
                <tr>
                    @foreach($headers as $header)
                    <th class="small text-uppercase text-muted fw-semibold px-3 py-2">{{ $header }}</th>
                    @endforeach
                </tr>
            </thead>
            @endif
            <tbody>
                @if($rows && $rows->count() > 0)
                    @if($rowComponent)
                        @foreach($rows as $row)
                            <x-dynamic-component
                                :component="$rowComponent"
                                :$row
                                :$routePrefix
                                :$totalRequiredDocs
                            />
                        @endforeach
                    @else
                        {{ $slot ?? '' }}
                    @endif
                @else
                    <tr>
                        <td colspan="{{ count($headers) ?: 1 }}" class="text-center py-5 text-muted small">
                            <i class="fas fa-inbox fa-3x mb-3 d-block opacity-25"></i>
                            {{ $emptyMessage }}
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
    @if($pagination && $pagination->hasPages())
    <div class="card-footer bg-white border-0 px-4 py-3">
        {{ $pagination->links() }}
    </div>
    @endif
</div>
