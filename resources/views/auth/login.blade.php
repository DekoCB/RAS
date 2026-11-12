<x-guest-layout title="Iniciar sesión">
    <div class="auth-card">
        <div class="auth-card__left">
            <a class="auth-brand" href="/">
                <x-brand-logo class="auth-brand__logo" />
                <span class="auth-brand__text">
                    <span class="auth-brand__title">R.A.S</span>
                </span>
            </a>

            <h1 class="auth-hero__title">¡Bienvenido!</h1>
            <p class="auth-hero__lead">
                Acceso seguro para la comunidad educativa. Gestiona clases, matrículas,
                pagos y reportes desde un solo lugar.
            </p>

            <div class="auth-hero__pill">Valores: Estudio - Trabajo - Disciplina</div>

            <div class="auth-hero__stats">
                <div>
                    <span class="auth-stat__num">25+</span>
                    <span class="auth-stat__label">Años de experiencia</span>
                </div>
                <div>
                    <span class="auth-stat__num">3</span>
                    <span class="auth-stat__label">Niveles educativos</span>
                </div>
            </div>
        </div>

        <div class="auth-card__right">
            <a class="auth-brand auth-brand--mobile" href="/">
                <x-brand-logo class="auth-brand__logo" />
                <span class="auth-brand__text">
                    <span class="auth-brand__title">R.A.S</span>
                </span>
            </a>

            <div class="auth-card__header">
                <h2>Ingresar</h2>
                <p>Usa tu DNI o usuario para continuar.</p>
            </div>

            @if (session('status'))
                <div class="auth-alert">{{ session('status') }}</div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="auth-form">
                @csrf

                <label for="dni">DNI o usuario</label>
                <input id="dni" type="text" name="dni" value="{{ old('dni') }}" required autofocus
                    autocomplete="username" placeholder="Ingrese su DNI o usuario">
                @error('dni')
                    <div class="auth-error">{{ $message }}</div>
                @enderror

                <label for="password">Contraseña</label>
                <input id="password" type="password" name="password" required autocomplete="current-password"
                    placeholder="********">
                @error('password')
                    <div class="auth-error">{{ $message }}</div>
                @enderror

                <div class="auth-row">
                    <label class="auth-check auth-check--disabled">
                        <input id="remember_me" type="checkbox" name="remember" disabled>
                        <span>Recordarme</span>
                    </label>

                    <span class="auth-link auth-link--disabled" aria-disabled="true">¿Olvidaste tu contraseña?</span>
                </div>

                <button type="submit" class="auth-btn">Ingresar</button>
            </form>

        </div>
    </div>
</x-guest-layout>
