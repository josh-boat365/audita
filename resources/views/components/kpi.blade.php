<x-base-layout>
    <div class="kpi">
    {{--  <h2>{{ $kpi->kpiName }}</h2>
    <p>{{ $kpi->kpiDescription }}</p>  --}}
    {{--  <p>Score: {{ $kpi->kpiScore }}</p>  --}}

    @foreach ($kpi->sections as $section)
        <x-section :section="$section" :kpi="$kpi" />
    @endforeach
</div>
</x-base-layout>
