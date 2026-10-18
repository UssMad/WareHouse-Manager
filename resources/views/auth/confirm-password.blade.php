<x-guest-layout>
    <div class="mb-7"><p class="form-eyebrow text-cyan-700">Confirmation requise</p><h1 class="mt-2 text-2xl font-semibold tracking-tight text-slate-900">Vérifiez votre identité.</h1><p class="mt-2 text-sm leading-6 text-slate-500">Entrez votre mot de passe avant de poursuivre.</p></div>
    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-5">@csrf<div><x-input-label for="password" value="Mot de passe" /><x-text-input id="password" class="w-full" type="password" name="password" required autocomplete="current-password" /><x-input-error :messages="$errors->get('password')" /></div><x-primary-button class="w-full">Confirmer</x-primary-button></form>
</x-guest-layout>
