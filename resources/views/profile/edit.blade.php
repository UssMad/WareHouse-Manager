<x-app-layout>
    <div class="mb-8"><p class="page-kicker">Espace personnel</p><h1 class="page-title mt-2">Mon profil</h1><p class="mt-2 text-sm text-slate-600">Gérez vos informations de connexion et la sécurité de votre compte.</p></div>
    <div class="max-w-3xl space-y-7"><section class="form-shell">@include('profile.partials.update-profile-information-form')</section><section class="form-shell">@include('profile.partials.update-password-form')</section><section class="border border-red-200 bg-white p-6 sm:p-8">@include('profile.partials.delete-user-form')</section></div>
</x-app-layout>
