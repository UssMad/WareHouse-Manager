<x-guest-layout>
    <div class="mb-7"><p class="form-eyebrow text-cyan-700">Accès sécurisé</p><h1 class="mt-2 text-2xl font-semibold tracking-tight text-slate-900">Bon retour.</h1><p class="mt-2 text-sm leading-6 text-slate-500">Connectez-vous pour retrouver votre inventaire.</p></div>
    <x-auth-session-status :status="session('status')" />
    <form method="POST" action="{{ route('login') }}" class="space-y-5">@csrf
        <div><x-input-label for="email" value="Adresse e-mail" /><x-text-input id="email" class="w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" /><x-input-error :messages="$errors->get('email')" /></div>
        <div><div class="flex items-center justify-between"><x-input-label for="password" value="Mot de passe" />@if (Route::has('password.request'))<a class="text-xs font-semibold text-cyan-700 hover:text-cyan-900" href="{{ route('password.request') }}">Mot de passe oublié ?</a>@endif</div><x-text-input id="password" class="w-full" type="password" name="password" required autocomplete="current-password" /><x-input-error :messages="$errors->get('password')" /></div>
        <label class="flex cursor-pointer items-center gap-2 text-sm text-slate-600"><input id="remember_me" type="checkbox" class="rounded border-slate-300 text-cyan-700 shadow-sm focus:ring-cyan-600" name="remember">Se souvenir de moi</label>
        <x-primary-button class="w-full">Se connecter</x-primary-button>
    </form>
    <p class="mt-7 border-t border-slate-200 pt-5 text-center text-sm text-slate-600">Nouveau sur StockManager ? <a href="{{ route('register') }}" class="font-semibold text-cyan-700 hover:text-cyan-900">Créer un compte</a></p>
</x-guest-layout>
