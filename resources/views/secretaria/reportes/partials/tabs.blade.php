@php
    $activeTab = $active ?? 'pagos-dia';
@endphp

<div class="jp-product-tabs rep-tabs">
    <a class="jp-product-tab {{ $activeTab === 'pagos-dia' ? 'is-active' : '' }}" href="{{ route('secretaria.reportes.pagos-dia') }}">
        <i data-feather="calendar"></i>
        Pagos del dia
    </a>
    <a class="jp-product-tab {{ $activeTab === 'morosos' ? 'is-active' : '' }}" href="{{ route('secretaria.reportes.morosos') }}">
        <i data-feather="alert-circle"></i>
        Morosos
    </a>
    <a class="jp-product-tab {{ $activeTab === 'pensiones' ? 'is-active' : '' }}" href="{{ route('secretaria.reportes.pensiones') }}">
        <i data-feather="bar-chart-2"></i>
        Pensiones
    </a>
    <a class="jp-product-tab {{ $activeTab === 'alumno' ? 'is-active' : '' }}" href="{{ route('secretaria.reportes.alumno') }}">
        <i data-feather="user"></i>
        Alumno
    </a>
</div>
