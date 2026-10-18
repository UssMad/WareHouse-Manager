<x-guest-layout>
    <div class="mb-7"><p class="form-eyebrow text-cyan-700">Récupération</p><h1 class="mt-2 text-2xl font-semibold tracking-tight text-slate-900">Réinitialiser l’accès.</h1><p class="mt-2 text-sm leading-6 text-slate-500">Indiquez votre e-mail. Nous vous enverrons un lien de réinitialisation.</p></div>
    <x-auth-session-status :status="session('status')" />
    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">@csrf<div><x-input-label for="email" value="Adresse e-mail" /><x-text-input id="email" class="w-full" type="email" name="email" :value="old('email')" required autofocus /><x-input-error :messages="$errors->get('email')" /></div><x-primary-button class="w-full">Envoyer le lien</x-primary-button></form>
    <p class="mt-7 text-center text-sm"><a href="{{ route('login') }}" class="font-semibold text-cyan-700 hover:text-cyan-900">← Retour à la connexion</a></p>
</x-guest-layout>
