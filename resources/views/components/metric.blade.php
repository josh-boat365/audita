<x-base-layout>
    <div class="metric">
        <h5>{{ $metric->metricName }}</h5>
        <p>{{ $metric->metricDescription }}</p>
        <p>Score: {{ $metric->metricScore }}</p>

        <form action="{{ route('self.rating') }}" method="POST" class="metric-form">
            @csrf
            <div class="d-flex gap-3">
                <input type="hidden" name="metricId" value="{{ $metric->metricId }}">
                {{--  <input type="hidden" name="kpiId" value="{{ $metric->kpiId }}">  --}}
                <div class="col-md-2">
                    <input class="form-control mb-3" type="number" name="metricEmpScore" required
                        placeholder="Enter Score" @disabled(isset($metric->metricEmpScore) &&
                                ($metric->metricEmpScore->status === 'REVIEW' || $metric->metricEmpScore->status === 'CONFIRMATION'))
                        value="{{ optional($metric->metricEmpScore)->metricEmpScore ?? '' }}">
                </div>
                <div class="col-md-9">
                    <input class="form-control mb-3" type="text" name="metricComment"
                        placeholder="Enter your comments" @disabled(isset($metric->metricEmpScore) &&
                                ($metric->metricEmpScore->status === 'REVIEW' || $metric->metricEmpScore->status === 'CONFIRMATION'))
                        value="{{ optional($metric->metricEmpScore)->metricComment ?? '' }}">
                </div>
                @if (
                    !isset($metric->metricEmpScore) ||
                        ($metric->metricEmpScore->status !== 'REVIEW' && $metric->metricEmpScore->status !== 'CONFIRMATION'))
                    <button type="submit" class="btn btn-success">Save</button>
                @endif
            </div>
            <input type="hidden" name="kpiType" value="{{ $kpi->kpiType }}">
            <input type="hidden" name="metricEmpScoreId" value="{{ $metric->metricEmpScore->id ?? '' }}">
            <input type="hidden" name="metricId" value="{{ $metric->metricId }}">
            <input type="hidden" name="sectionId" value="{{ $section->sectionId }}">
            <input type="hidden" name="kpiId" value="{{ $kpi->kpiId }}">
            <button type="submit" style="height: fit-content" class="btn btn-primary">Save</button>
        </form>
    </div>
</x-base-layout>
