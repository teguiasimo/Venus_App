<?php

use App\Models\User;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Mary\Traits\Toast;
use Livewire\Attributes\Title;

new #[Layout('components.layouts.app')]
	#[Title('Mon Profil')]
 class extends Component {
    use Toast;

    public User $user;

    // Profile Data
    public string $name = '';
    public string $email = '';

    // Password Data
    public string $password = '';
    public string $password_confirmation = '';

    public array $breadcrumbs = [];

    public function mount(User $user): void
    {
        $this->user = $user;
        $this->name = $user->name;
        $this->email = $user->email;

        $this->breadcrumbs = [
            ['label' => 'Accueil', 'link' => '/'],
            ['label' => 'Mon Profil'],
        ];
    }

    public function updateProfile(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($this->user->id)],
        ]);

        $this->user->update($validated);
        $this->success('Profil mis à jour avec succès.');
    }

    public function updatePassword(): void
    {
        $validated = $this->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $this->user->update([
            'password' => bcrypt($validated['password']),
        ]);

        $this->reset(['password', 'password_confirmation']);
        $this->success('Mot de passe mis à jour avec succès.');
    }
}; ?>

<div>
    <x-breadcrumbs :items="$breadcrumbs" class="mb-4" />
    <x-header title="Mon Profil" separator />

    <div class="grid gap-8 lg:grid-cols-2">
        {{-- Profile Information --}}
        <div class="col-span-1">
            <x-card title="Informations Personnelles" shadow separator>
                <x-form wire:submit="updateProfile">
                    <x-input label="Nom" wire:model="name" icon="o-user" />
                    <x-input label="Email" type="email" wire:model="email" icon="o-envelope" />

                    <x-slot:actions>
                        <x-button label="Enregistrer" class="btn-primary" type="submit" spinner="updateProfile" />
                    </x-slot:actions>
                </x-form>
            </x-card>
        </div>

        {{-- Password Update --}}
        <div class="col-span-1">
            <x-card title="Changer de mot de passe" shadow separator>
                <x-form wire:submit="updatePassword">
                    <x-input label="Nouveau mot de passe" type="password" wire:model="password" icon="o-key" />
                    <x-input label="Confirmer le mot de passe" type="password" wire:model="password_confirmation" icon="o-key" />

                    <x-slot:actions>
                        <x-button label="Changer le mot de passe" class="btn-warning" type="submit" spinner="updatePassword" />
                    </x-slot:actions>
                </x-form>
            </x-card>
        </div>
    </div>
</div>