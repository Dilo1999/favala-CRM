<?php

namespace App\Http\Livewire\Concerns;

use App\Models\Delivery;

/**
 * Edit (location/contact) and Reschedule (deadline date+time) actions for
 * deliveries — shared by the list page and the detail page so the row-menu
 * ("Edit, Print, Reschedule, Mark as Complete, Delete") is consistent everywhere.
 */
trait EditsDeliveries
{
    public ?int $deliveryEditingId = null;

    public array $deliveryEditForm = ['location' => '', 'contact_name' => '', 'contact_phone' => ''];

    public ?int $deliveryReschedulingId = null;

    public array $deliveryRescheduleForm = ['deadline_date' => '', 'deadline_time' => null];

    public function editDelivery(int $id): void
    {
        $delivery = Delivery::findOrFail($id);

        $this->deliveryEditingId = $id;
        $this->deliveryEditForm = [
            'location' => $delivery->location,
            'contact_name' => $delivery->contact_name,
            'contact_phone' => $delivery->contact_phone,
        ];
    }

    public function closeDeliveryEdit(): void
    {
        $this->deliveryEditingId = null;
    }

    public function saveDeliveryEdit(): void
    {
        $this->validate([
            'deliveryEditForm.location' => 'required|string|max:191',
            'deliveryEditForm.contact_name' => 'nullable|string|max:191',
            'deliveryEditForm.contact_phone' => 'nullable|string|max:60',
        ]);

        Delivery::findOrFail($this->deliveryEditingId)->update($this->deliveryEditForm);

        $this->deliveryEditingId = null;
        session()->flash('status', 'Delivery updated.');
        $this->afterDeliveryChange();
    }

    public function rescheduleDelivery(int $id): void
    {
        $delivery = Delivery::findOrFail($id);

        $this->deliveryReschedulingId = $id;
        $this->deliveryRescheduleForm = [
            'deadline_date' => optional($delivery->deadline_date)->toDateString() ?? now()->toDateString(),
            'deadline_time' => $delivery->deadline_time,
        ];
    }

    public function closeDeliveryReschedule(): void
    {
        $this->deliveryReschedulingId = null;
    }

    public function saveDeliveryReschedule(): void
    {
        $this->validate([
            'deliveryRescheduleForm.deadline_date' => 'required|date',
            'deliveryRescheduleForm.deadline_time' => 'nullable',
        ]);

        Delivery::findOrFail($this->deliveryReschedulingId)->update([
            'deadline_date' => $this->deliveryRescheduleForm['deadline_date'],
            'deadline_time' => $this->deliveryRescheduleForm['deadline_time'] ?: null,
        ]);

        $this->deliveryReschedulingId = null;
        session()->flash('status', 'Delivery rescheduled.');
        $this->afterDeliveryChange();
    }

    /** Overridden by Show.php to refresh its hydrated $record after an edit/reschedule. */
    protected function afterDeliveryChange(): void
    {
        //
    }
}
