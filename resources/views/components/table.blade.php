@props(['id' => 'table', 'striped' => true, 'hover' => true, 'responsive' => true])
<div class="{{ $responsive ? 'table-responsive' : '' }}">
    <table class="table mb-0 {{ $striped ? 'table-striped' : '' }} {{ $hover ? 'table-hover' : '' }} align-middle" id="{{ $id }}">
        @if(isset($thead) && !empty(trim($thead ?? '')))
        <thead class="table-light">
            {{ $thead }}
        </thead>
        @endif
        <tbody>
            {{ $slot }}
        </tbody>
        @if(isset($tfoot))
        <tfoot>
            {{ $tfoot }}
        </tfoot>
        @endif
    </table>
</div>
