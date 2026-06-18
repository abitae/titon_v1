<?php

namespace App\Livewire\Frontend;

use App\Concerns\InteractsWithToast;
use App\Models\ContactMessage;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class ManageContactMessages extends Component
{
    use InteractsWithToast, WithPagination;

    public string $title = 'Mensajes de contacto';

    public string $search = '';

    public string $readFilter = 'all';

    public ?ContactMessage $selectedMessage = null;

    public bool $showDetailModal = false;

    public function mount(): void
    {
        $this->authorizeSuperAdmin();
    }

    public function render(): View
    {
        $messages = ContactMessage::query()
            ->when($this->search !== '', function ($query): void {
                $query->where(function ($nestedQuery): void {
                    $nestedQuery
                        ->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('email', 'like', '%'.$this->search.'%')
                        ->orWhere('subject', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->readFilter === 'unread', fn ($query) => $query->unread())
            ->when($this->readFilter === 'read', fn ($query) => $query->whereNotNull('read_at'))
            ->latest()
            ->paginate(15);

        return view('livewire.frontend.manage-contact-messages', [
            'messages' => $messages,
        ])->layout('layouts.app', ['title' => $this->title]);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedReadFilter(): void
    {
        $this->resetPage();
    }

    public function openDetailModal(int $messageId): void
    {
        $this->selectedMessage = ContactMessage::query()->findOrFail($messageId);
        $this->selectedMessage->markAsRead();
        $this->showDetailModal = true;
    }

    public function markAsRead(int $messageId): void
    {
        $this->authorizeSuperAdmin();

        ContactMessage::query()->findOrFail($messageId)->markAsRead();
        $this->successToast('Mensaje marcado como leído.');
    }

    public function deleteMessage(int $messageId): void
    {
        $this->authorizeSuperAdmin();

        ContactMessage::query()->findOrFail($messageId)->delete();
        $this->showDetailModal = false;
        $this->selectedMessage = null;
        $this->resetPage();
        $this->warningToast('Mensaje eliminado correctamente.');
    }

    public function closeModal(): void
    {
        $this->showDetailModal = false;
        $this->selectedMessage = null;
    }

    protected function authorizeSuperAdmin(): void
    {
        abort_unless(Auth::user()?->hasRole('Super Admin'), 403);
    }
}
