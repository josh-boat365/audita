<x-base-layout>
    <div class="section-card" style="margin-top: 2rem;">
        <h4>{{ $section->sectionName }} (<span style="color: #c80f0f">{{ $section->sectionScore }}</span>)</h4>
        <p>{{ $section->sectionDescription }}</p>

        @if (isset($section->metrics) && $section->metrics->isEmpty())
            <form action="{{ route('self.rating') }}" method="POST" class="section-form">
                @csrf
                <div class="d-flex gap-3">
                    <div class="col-md-2">
                        <input class="form-control mb-3 score-input" type="number" name="sectionEmpScore" required
                            placeholder="Enter Score" max="{{ $section->sectionScore }}" @disabled(isset($section->sectionEmpScore) &&
                                    ($section->sectionEmpScore->status === 'REVIEW' || $section->sectionEmpScore->status === 'CONFIRMATION'))
                            title="The Score cannot be more than the section score {{ $section->sectionScore }}"
                            value="{{ optional($section->sectionEmpScore)->sectionEmpScore ?? '' }}">
                    </div>
                    <div class="col-md-9">
                        <input class="form-control mb-3 comment-input" type="text" name="employeeComment"
                            placeholder="Enter your comments" @disabled(isset($section->sectionEmpScore) &&
                                    ($section->sectionEmpScore->status === 'REVIEW' || $section->sectionEmpScore->status === 'CONFIRMATION'))
                            value="{{ optional($section->sectionEmpScore)->employeeComment ?? '' }}">
                    </div>
                    @if (
                        !isset($section->sectionEmpScore) ||
                            ($section->sectionEmpScore->status !== 'REVIEW' && $section->sectionEmpScore->status !== 'CONFIRMATION'))
                        <input type="hidden" name="kpiType" value="{{ $kpiType }}">
                        <input type="hidden" name="sectionEmpScoreId"
                            value="{{ $section->sectionEmpScore->id ?? '' }}">
                        <input type="hidden" name="sectionId" value="{{ $section->sectionId }}">
                        <input type="hidden" name="kpi Id" value="{{ $section->kpiId }}">
                        <button type="submit" class="btn btn-success">Save</button>
                    @endif
                </div>
            </form>
        @else
            @foreach ($section->metrics as $metric)
                <x-metric :metric="$metric" :section="$section" :kpi="$kpi" />
            @endforeach
        @endif
    </div>
</x-base-layout>
