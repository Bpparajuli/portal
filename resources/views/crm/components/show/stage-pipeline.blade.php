{{-- resources/views/crm/components/show/stage-pipeline.blade.php --}}
<div class="stage-pipeline">
    <div class="stage-track">
        @foreach ($stages as $index => $stg)
            @php
                $isCurrent = $currentStage?->id === $stg->id;
                $isPassed = $currentStage && $stg->stage_order < $currentStage->stage_order;
                $statusClass = $isCurrent ? 'current' : ($isPassed ? 'passed' : 'pending');
            @endphp
            <div class="stage-wrapper">
                @if ($canEdit)
                    <form action="{{ route('crm.student.stage', $student) }}" method="POST">
                        @csrf
                        <input type="hidden" name="new_stage_id" value="{{ $stg->id }}">
                        <button type="submit" class="stage-card {{ $statusClass }}"
                            onclick="return confirm('Move to \'{{ $stg->name }}\'?')">
                            <span class="stage-title">{{ $stg->name }}</span>
                        </button>
                    </form>
                @else
                    <div class="stage-card {{ $statusClass }}">
                        <span class="stage-title">{{ $stg->name }}</span>
                        @if ($isCurrent)
                            <span class="stage-days">{{ $student->days_in_current_stage ?? 0 }}d</span>
                        @endif
                    </div>
                @endif
            </div>
        @endforeach
    </div>
</div>
