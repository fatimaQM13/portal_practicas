<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        {{ __('Verificación de Administrador: ingresa el código de 6 dígitos generado para tu sesión. Tiene una vigencia de 5 minutos.') }}
    </div>

    @if (session('status'))
        <div class="mb-4 rounded-md bg-blue-50 p-3 text-sm font-medium text-blue-700">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.verify.store') }}">
        @csrf

        <div>
            <x-input-label for="code" :value="__('Código de 6 dígitos')" />
            <x-text-input
                id="code"
                class="mt-1 block w-full tracking-widest text-center text-lg"
                type="text"
                name="code"
                maxlength="6"
                inputmode="numeric"
                required
                autofocus
            />
            <x-input-error :messages="$errors->get('code')" class="mt-2" />
        </div>

        <div class="mt-4 flex items-center justify-between">
            <a href="{{ route('login') }}" class="text-sm text-gray-600 underline hover:text-gray-900">
                {{ __('Volver al login') }}
            </a>

            <x-primary-button>
                {{ __('Verificar código') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>