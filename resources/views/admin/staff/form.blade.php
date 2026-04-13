<x-admin-layout>
    <x-slot name="header">
        <h2 class="text-lg font-semibold text-gray-800">
            {{ $staff ? __('staff.edit_staff') : __('staff.add_staff') }}
        </h2>
    </x-slot>

    <form method="POST"
          action="{{ $staff ? route('admin.staff.update', $staff) : route('admin.staff.store') }}"
          x-data="staffForm({{ json_encode(array_keys(\App\Support\StaffPermissions::PRESETS)) }})"
          @preset.window="applyPreset($event.detail.role)">
        @csrf
        @if ($staff) @method('PUT') @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- ── Left column: profile + role ─────────────────────────────── --}}
            <div class="lg:col-span-1 space-y-6">

                {{-- Profile card --}}
                <div class="bg-white rounded-xl shadow-sm p-6 space-y-4">
                    <h3 class="text-sm font-semibold text-gray-700">{{ __('staff.profile') }}</h3>

                    <div>
                        <label class="form-label">{{ __('common.name') }} <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $staff?->name) }}"
                               class="form-input" required>
                        @error('name') <p class="form-error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="form-label">{{ __('common.email') }} <span class="text-red-500">*</span></label>
                        <input type="email" name="email" value="{{ old('email', $staff?->email) }}"
                               class="form-input" required>
                        @error('email') <p class="form-error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="form-label">
                            {{ __('common.password') }}
                            @if ($staff) <span class="text-gray-400 text-xs">({{ __('common.leave_blank_to_keep') }})</span> @else <span class="text-red-500">*</span> @endif
                        </label>
                        <input type="password" name="password" class="form-input"
                               {{ $staff ? '' : 'required' }} autocomplete="new-password">
                        @error('password') <p class="form-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="is_active" value="1" id="is_active"
                               class="rounded text-indigo-600"
                               {{ old('is_active', $staff ? $staff->is_active : true) ? 'checked' : '' }}>
                        <label for="is_active" class="text-sm text-gray-700">{{ __('staff.active_account') }}</label>
                    </div>
                </div>

                {{-- Role card --}}
                <div class="bg-white rounded-xl shadow-sm p-6 space-y-4">
                    <h3 class="text-sm font-semibold text-gray-700">{{ __('staff.role') }}</h3>

                    <div>
                        <select name="role" id="role_select" class="form-input"
                                x-model="role"
                                @change="onRoleChange($event.target.value)">
                            <option value="super_admin" {{ old('role', $staff?->role) === 'super_admin' ? 'selected' : '' }}>
                                {{ __('staff.role_super_admin') }}
                            </option>
                            @foreach ($presets as $preset)
                                <option value="{{ $preset }}" {{ old('role', $staff?->role) === $preset ? 'selected' : '' }}>
                                    {{ __('staff.role_' . $preset) }}
                                </option>
                            @endforeach
                        </select>

                        <p class="text-xs text-gray-400 mt-2" x-show="role === 'super_admin'">
                            {{ __('staff.super_admin_note') }}
                        </p>
                    </div>

                    {{-- Preset quick-load buttons --}}
                    <div x-show="role !== 'super_admin'" class="space-y-2">
                        <p class="text-xs font-medium text-gray-500">{{ __('staff.load_preset') }}</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($presets as $preset)
                                <button type="button"
                                        @click="loadPreset('{{ $preset }}')"
                                        class="text-xs px-3 py-1 rounded-full border border-gray-300 hover:border-indigo-400 hover:text-indigo-600 transition">
                                    {{ __('staff.role_' . $preset) }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>

            </div>

            {{-- ── Right column: permission matrix ─────────────────────────── --}}
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-sm p-6" x-show="role !== 'super_admin'">
                    <div class="flex items-center justify-between mb-5">
                        <h3 class="text-sm font-semibold text-gray-700">{{ __('staff.permissions') }}</h3>
                        <div class="flex gap-3 text-xs">
                            <button type="button" @click="selectAll()"
                                    class="text-indigo-600 hover:underline">{{ __('common.select_all') }}</button>
                            <button type="button" @click="selectNone()"
                                    class="text-gray-500 hover:underline">{{ __('common.select_none') }}</button>
                        </div>
                    </div>

                    <div class="space-y-6">
                        @foreach ($grouped as $area => $permissions)
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                        {{ __('staff.area_' . $area) }}
                                    </h4>
                                    <button type="button"
                                            @click="toggleArea({{ json_encode(array_keys($permissions)) }})"
                                            class="text-xs text-gray-400 hover:text-indigo-600">
                                        {{ __('common.toggle') }}
                                    </button>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    @foreach ($permissions as $slug => $label)
                                        <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer select-none">
                                            <input type="checkbox"
                                                   name="permissions[]"
                                                   value="{{ $slug }}"
                                                   class="rounded text-indigo-600 permission-checkbox"
                                                   data-slug="{{ $slug }}"
                                                   x-model="granted"
                                                   :value="'{{ $slug }}'"
                                                   {{ in_array($slug, old('permissions', $effective ?? [])) ? 'checked' : '' }}>
                                            {{ $label }}
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div x-show="role === 'super_admin'"
                     class="bg-purple-50 border border-purple-200 rounded-xl p-6 text-sm text-purple-700">
                    {{ __('staff.super_admin_all_access') }}
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="mt-6 flex items-center gap-4">
            <x-button type="submit">{{ $staff ? __('common.save_changes') : __('staff.create_account') }}</x-button>
            <a href="{{ route('admin.staff.index') }}" class="text-sm text-gray-500 hover:text-gray-700">
                {{ __('common.cancel') }}
            </a>
        </div>
    </form>

    @push('scripts')
    <script>
    const PRESETS = @json(\App\Support\StaffPermissions::PRESETS);

    function staffForm() {
        return {
            role: '{{ old('role', $staff?->role ?? 'admin') }}',
            granted: @json(old('permissions', $effective ?? [])),

            onRoleChange(role) {
                // Don't auto-load preset when editing existing staff — only on create
                @if (! $staff)
                    if (role !== 'super_admin' && PRESETS[role]) {
                        this.granted = PRESETS[role];
                    }
                @endif
            },

            loadPreset(role) {
                if (PRESETS[role]) {
                    this.granted = [...PRESETS[role]];
                    this.role = role;
                }
            },

            selectAll() {
                this.granted = @json(\App\Support\StaffPermissions::all());
            },

            selectNone() {
                this.granted = [];
            },

            toggleArea(slugs) {
                const allChecked = slugs.every(s => this.granted.includes(s));
                if (allChecked) {
                    this.granted = this.granted.filter(s => !slugs.includes(s));
                } else {
                    slugs.forEach(s => {
                        if (!this.granted.includes(s)) this.granted.push(s);
                    });
                }
            },
        };
    }
    </script>
    @endpush
</x-admin-layout>
