<x-portal.layouts.guest>
    @if ($errors->any())
        <div class="alert-error">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('portal.login') }}">
        @csrf

        <!-- Email -->
        <div class="mb-4">
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                   class="dark-input" placeholder="tu@email.com">
        </div>

        <!-- Password -->
        <div class="mb-4">
            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
            <input id="password" type="password" name="password" required
                   class="dark-input" placeholder="Tu password">
        </div>

        <!-- Remember Me -->
        <div class="mb-6">
            <label class="inline-flex items-center">
                <input type="checkbox" name="remember" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                <span class="ml-2 text-sm text-gray-600">Recordarme</span>
            </label>
        </div>

        <!-- Submit -->
        <button type="submit" class="btn-primary">
            Iniciar Sesion
        </button>
    </form>
</x-portal.layouts.guest>
