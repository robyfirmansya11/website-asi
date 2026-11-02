<x-layouts::auth title="Lupa password">
    <div class="flex flex-col gap-6">
        <x-auth-header title="Lupa password" description="Masukkan email akun website Anda yang terdaftar dengan domain @artabumisentraindustri.com." />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('password.email') }}" class="flex flex-col gap-6">
            @csrf

            <!-- Email Address -->
            <flux:input
                name="email"
                label="Email perusahaan"
                :value="old('email')"
                type="email"
                required
                autofocus
                autocomplete="email"
                placeholder="it@artabumisentraindustri.com"
            />

            <flux:button variant="primary" type="submit" class="w-full" data-test="email-password-reset-link-button">
                Kirim Tautan Reset Password
            </flux:button>
        </form>

        <p class="text-sm text-zinc-600 dark:text-zinc-400">Buka inbox atau folder spam di Webmail setelah mengirim permintaan. Tautan ini mengubah password website, bukan password email Anda.</p>

        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-400">
            <span>Kembali ke</span>
            <flux:link :href="route('login')" wire:navigate>halaman login</flux:link>
        </div>
    </div>
</x-layouts::auth>
