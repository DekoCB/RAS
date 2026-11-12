@if (session('status'))
    <div class="ad-alert ad-alert--info">
        <i data-feather="info"></i>
        <div>{{ session('status') }}</div>
    </div>
@endif

@if ($errors->any())
    <div class="ad-alert ad-alert--danger">
        <i data-feather="alert-circle"></i>
        <div>
            <strong>Revisa lo siguiente:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif
