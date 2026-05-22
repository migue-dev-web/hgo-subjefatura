<x-app-layout>
    <div class="max-w-3xl mx-auto py-6">
        <h1 class="text-2xl font-bold text-gray-800 mb-4">
            Nuevo usuario
        </h1>

        <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-6">
            @csrf

            {{-- Nombre --}}
            <div>
                <x-input-label for="name" value="Nombre completo" />
                <x-text-input
                    id="name"
                    name="name"
                    type="text"
                    class="mt-1 block w-full"
                    value="{{ old('name') }}"
                    required
                    autofocus
                />
                <x-input-error for="name" class="mt-1" />
            </div>

            {{-- Correo electrónico --}}
            <div>
                <x-input-label for="email" value="Correo electrónico" />
                <x-text-input
                    id="email"
                    name="email"
                    type="email"
                    class="mt-1 block w-full"
                    value="{{ old('email') }}"
                    required
                />
                <x-input-error for="email" class="mt-1" />
            </div>

            {{-- Contraseñas --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <x-input-label for="password" value="Contraseña" />
                    <x-text-input
                        id="password"
                        name="password"
                        type="password"
                        class="mt-1 block w-full"
                        required
                        autocomplete="new-password"
                    />
                    <x-input-error for="password" class="mt-1" />
                </div>

                <div>
                    <x-input-label for="password_confirmation" value="Confirmar contraseña" />
                    <x-text-input
                        id="password_confirmation"
                        name="password_confirmation"
                        type="password"
                        class="mt-1 block w-full"
                        required
                        autocomplete="new-password"
                    />
                </div>
            </div>

          {{-- Sección y Nivel de Acceso --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-4 border-t border-gray-100 pt-4">
    @if(auth()->user()->profile?->nivel === 3)
        {{-- Nivel 3 ve los selects --}}
        <div>
            <x-input-label for="seccion" value="Sección asignada" />
            <select id="seccion" name="seccion" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" required>
                <option value="general" @selected(old('seccion', 'general') == 'general')>General</option>
                <option value="rh" @selected(old('seccion') == 'rh')>RRHH</option>
                <option value="Quirofano" @selected(old('seccion') == 'Quirofano')>Quirofano</option>
                <option value="ConExt" @selected(old('seccion') == 'ConExt')>Consulta externa</option>
                <option value="autoclaves" @selected(old('seccion') == 'autoclaves')>Autoclaves</option>
                <option value="TOCO-cir" @selected(old('seccion') == 'TOCO-cir')>TOCO Cirugia</option>

            </select>
            <x-input-error for="seccion" class="mt-1" />
        </div>

        <div>
            <x-input-label for="nivel" value="Nivel de acceso inicial" />
            <select id="nivel" name="nivel" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" required>
                <option value="1" @selected(old('nivel', 1) == 1)>Nivel 1: Consultor / Lector</option>
                <option value="2" @selected(old('nivel') == 2)>Nivel 2: Editor / Supervisor</option>
                <option value="3" @selected(old('nivel') == 3)>Nivel 3: Administrador Total</option>
            </select>
            <x-input-error for="nivel" class="mt-1" />
        </div>
    @else
        {{-- Nivel 2 ve indicaciones fijas --}}
        <div>
            <x-input-label value="Sección asignada automáticamente" />
            <div class="mt-2 px-3 py-2 bg-slate-50 border border-slate-200 text-indigo-700 rounded-md text-sm uppercase font-bold tracking-wider">
                {{ auth()->user()->profile?->seccion }}
            </div>
            <p class="text-[11px] text-gray-400 mt-1">Los nuevos usuarios pertenecen obligatoriamente a tu área.</p>
        </div>

        <div>
            <x-input-label value="Nivel otorgado por defecto" />
            <div class="mt-2 px-3 py-2 bg-slate-50 border border-slate-200 text-slate-700 rounded-md text-sm font-medium">
                Nivel 1 (Consultor / Lector)
            </div>
            <p class="text-[11px] text-gray-400 mt-1">Crearás un usuario operativo básico.</p>
        </div>
    @endif
</div>

            <div class="flex justify-end gap-2 pt-2">
                <a href="{{ route('admin.users.index') }}"
                   class="text-sm text-gray-600 hover:text-gray-800 self-center mr-2">
                    Cancelar
                </a>

                <x-primary-button>
                    Crear usuario
                </x-primary-button>
            </div>
        </form>
    </div>
</x-app-layout>