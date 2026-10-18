<x-guest-layout>
    <div class="mb-7"><p class="form-eyebrow text-cyan-700">Configuration initiale</p><h1 class="mt-2 text-2xl font-semibold tracking-tight text-slate-900">Créer votre espace.</h1><p class="mt-2 text-sm leading-6 text-slate-500">Quelques informations et votre inventaire est prêt.</p></div>
    <form method="POST" action="{{ route('register') }}" class="space-y-5">@csrf
        <div><x-input-label for="name" value="Nom complet" /><x-text-input id="name" class="w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" /><x-input-error :messages="$errors->get('name')" /></div>
        <div><x-input-label for="email" value="Adresse e-mail" /><x-text-input id="email" class="w-full" type="email" name="email" :value="old('email')" required autocomplete="username" /><x-input-error :messages="$errors->get('email')" /></div>
        <div><x-input-label for="password" value="Mot de passe" /><x-text-input id="password" class="w-full" type="password" name="password" required autocomplete="new-password" /><p class="form-hint">8 caractères minimum.</p><x-input-error :messages="$errors->get('password')" /></div>
        <div><x-input-label for="password_confirmation" value="Confirmer le mot de passe" /><x-text-input id="password_confirmation" class="w-full" type="password" name="password_confirmation" required autocomplete="new-password" /><x-input-error :messages="$errors->get('password_confirmation')" /></div>
        <x-primary-button class="w-full">Créer mon espace</x-primary-button>
    </form>
    <p class="mt-7 border-t border-slate-200 pt-5 text-center text-sm text-slate-600">Déjà inscrit ? <a href="{{ route('login') }}" class="font-semibold text-cyan-700 hover:text-cyan-900">Se connecter</a></p>
</x-guest-layout>
