<?php

namespace App\Livewire\ContactMessages;

use App\Enums\StudyType;
use App\Models\ContactMessage;
use Flux\Flux;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Lists the messages submitted through the public contact form
 * (see App\Http\Controllers\ContactController) so the team can review them.
 */
#[Title('Mensajes de contacto')]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'buscar', history: true)]
    public string $search = '';

    #[Url(as: 'tipo', history: true)]
    public string $studyType = '';

    public ?int $selectedMessageId = null;

    /**
     * Reset to the first page whenever the search term changes.
     */
    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Reset to the first page whenever the study type filter changes.
     */
    public function updatingStudyType(): void
    {
        $this->resetPage();
    }

    /**
     * Open the detail modal for a given message.
     */
    public function show(int $contactMessageId): void
    {
        $this->selectedMessageId = $contactMessageId;

        Flux::modal('mensaje-detalle')->show();
    }

    #[Computed]
    public function selectedMessage(): ?ContactMessage
    {
        return $this->selectedMessageId
            ? ContactMessage::find($this->selectedMessageId)
            : null;
    }

    /**
     * @return array<int, StudyType>
     */
    #[Computed]
    public function studyTypes(): array
    {
        return StudyType::cases();
    }

    public function render(): View
    {
        return view('livewire.contact-messages.index', [
            'messages' => $this->messages(),
        ]);
    }

    /**
     * @return LengthAwarePaginator<int, ContactMessage>
     */
    private function messages(): LengthAwarePaginator
    {
        return ContactMessage::query()
            ->when(
                $this->search !== '',
                fn ($query) => $query->where(
                    fn ($query) => $query
                        ->whereLike('name', "%{$this->search}%")
                        ->orWhereLike('email', "%{$this->search}%")
                        ->orWhereLike('institution', "%{$this->search}%")
                )
            )
            ->when(
                $this->studyType !== '',
                fn ($query) => $query->where('study_type', $this->studyType)
            )
            ->latest()
            ->paginate(15);
    }
}
