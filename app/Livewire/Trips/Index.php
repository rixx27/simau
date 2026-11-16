<?php

namespace App\Livewire\Trips;

use App\Models\Trip;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Index extends Component
{
    public string $statusFilter = 'all'; // all, active, planned, completed

    // Create Modal state
    public bool $isCreateModalOpen = false;
    public string $name = '';
    public string $destination = '';
    public string $startDate = '';
    public string $endDate = '';
    public ?int $budget = 3000000;
    public string $notes = '';
    public string $status = 'planned';

    public function mount(): void
    {
        $this->startDate = Carbon::now()->format('Y-m-d');
        $this->endDate = Carbon::now()->addDays(3)->format('Y-m-d');
    }

    #[On('transaction-saved')]
    public function refreshTrips(): void
    {
        // Re-renders component
    }

    public function openCreateModal(): void
    {
        $this->reset(['name', 'destination', 'notes']);
        $this->startDate = Carbon::now()->format('Y-m-d');
        $this->endDate = Carbon::now()->addDays(3)->format('Y-m-d');
        $this->budget = 3000000;
        $this->status = 'planned';
        $this->isCreateModalOpen = true;
    }

    public function closeCreateModal(): void
    {
        $this->isCreateModalOpen = false;
    }

    public function saveTrip(): void
    {
        $this->validate([
            'name' => 'required|string|max:120',
            'destination' => 'required|string|max:100',
            'startDate' => 'required|date',
            'endDate' => 'required|date|after_or_equal:startDate',
            'budget' => 'required|numeric|min:10000',
            'status' => 'required|in:planned,active,completed',
            'notes' => 'nullable|string|max:500',
        ], [
            'name.required' => 'Nama perjalanan wajib diisi.',
            'destination.required' => 'Kota atau tempat destinasi wajib diisi.',
            'startDate.required' => 'Tanggal mulai perjalanan wajib dipilih.',
            'endDate.required' => 'Tanggal selesai perjalanan wajib dipilih.',
            'endDate.after_or_equal' => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
            'budget.min' => 'Plafon anggaran minimal Rp 10.000.',
        ]);

        $user = Auth::user() ?? User::first();
        if (!$user) {
            return;
        }

        $user->trips()->create([
            'name' => $this->name,
            'destination' => $this->destination,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
            'budget' => $this->budget,
            'status' => $this->status,
            'notes' => $this->notes ?: null,
            'cover_image' => 'https://images.unsplash.com/photo-1488646953014-85cb44e25828?auto=format&fit=crop&w=600&q=80',
        ]);

        $this->isCreateModalOpen = false;
        $this->dispatch('transaction-saved');
        $this->dispatch('notify', message: 'Rencana trip baru berhasil dibuat!');
    }

    public function completeTrip(int $tripId): void
    {
        $user = Auth::user() ?? User::first();
        $trip = Trip::where('user_id', $user?->id)->findOrFail($tripId);
        
        $trip->update(['status' => 'completed']);
        $this->dispatch('transaction-saved');
        $this->dispatch('notify', message: 'Trip "' . $trip->name . '" telah diselesaikan! Anda dapat melihat rekap pasca-trip sekarang.');
    }

    public function activateTrip(int $tripId): void
    {
        $user = Auth::user() ?? User::first();
        $trip = Trip::where('user_id', $user?->id)->findOrFail($tripId);
        
        $trip->update(['status' => 'active']);
        $this->dispatch('transaction-saved');
        $this->dispatch('notify', message: 'Trip "' . $trip->name . '" kini aktif! Pengeluaran liburan siap dicatat.');
    }

    public function render()
    {
        $user = Auth::user() ?? User::first();
        $userId = $user?->id;

        $tripsQuery = Trip::with(['transactions.category'])
            ->where('user_id', $userId)
            ->orderByRaw("CASE WHEN status = 'active' THEN 1 WHEN status = 'planned' THEN 2 ELSE 3 END")
            ->orderBy('start_date', 'desc');

        if ($this->statusFilter !== 'all') {
            $tripsQuery->where('status', $this->statusFilter);
        }

        $trips = $tripsQuery->get();

        $allUserTrips = Trip::where('user_id', $userId)->get();
        $totalTripBudget = $allUserTrips->sum('budget');
        $activeTripsCount = $allUserTrips->where('status', 'active')->count();
        $plannedTripsCount = $allUserTrips->where('status', 'planned')->count();
        $completedTripsCount = $allUserTrips->where('status', 'completed')->count();

        return view('livewire.trips.index', [
            'trips' => $trips,
            'totalTripBudget' => $totalTripBudget,
            'activeTripsCount' => $activeTripsCount,
            'plannedTripsCount' => $plannedTripsCount,
            'completedTripsCount' => $completedTripsCount,
        ]);
    }
}
