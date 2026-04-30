<?php

declare(strict_types=1);

namespace App\Services\Shared;

use App\Models\Event;
use App\Models\EventStaff;
use App\Models\Staff;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class EmailService
{
    /**
     * Create event_staff records for a batch of staff members and prepare
     * the invitation payload for email blasting.
     *
     * - Generates a unique UUID invitation_token per staff member.
     * - Skips staff members who are already enrolled in the event (idempotent).
     * - Returns a collection of arrays ready to pass to a Mailable / queue job.
     *
     * @param  string        $eventId  UUID of the event.
     * @param  array<string> $staffIds Array of staff UUIDs to invite.
     *
     * @return Collection<int, array<string, mixed>> Prepared invitation payloads.
     *
     * @throws NotFoundHttpException            If the event does not exist.
     * @throws UnprocessableEntityHttpException If the event is inactive.
     */
    public function sendBulkInvitation(string $eventId, array $staffIds): Collection
    {
        return DB::transaction(function () use ($eventId, $staffIds): Collection {

            // 1. Validate event exists and is active.
            /** @var Event|null $event */
            $event = Event::find($eventId);

            if (! $event) {
                throw new NotFoundHttpException('Event not found.');
            }

            if (! $event->is_active) {
                throw new UnprocessableEntityHttpException('Cannot send invitations for an inactive event.');
            }

            // 2. Fetch only the staff that belong to the same organizer.
            $staffMembers = Staff::whereIn('id', $staffIds)
                ->where('organizer_id', $event->organizer_id)
                ->get()
                ->keyBy('id');

            // 3. Find staff IDs already enrolled to avoid duplicates.
            $alreadyEnrolled = EventStaff::where('event_id', $eventId)
                ->whereIn('staff_id', $staffIds)
                ->pluck('staff_id')
                ->flip(); // O(1) lookup

            $payloads = collect();

            foreach ($staffMembers as $staff) {
                // Skip if already enrolled — this makes the method idempotent.
                if ($alreadyEnrolled->has($staff->id)) {
                    continue;
                }

                $token = (string) Str::uuid();

                EventStaff::create([
                    'event_id'         => $eventId,
                    'staff_id'         => $staff->id,
                    'invitation_token' => $token,
                    'is_attending'     => false,
                    'pax'              => 1,
                ]);

                // Build the payload the email job/Mailable will consume.
                $payloads->push([
                    'staff_id'         => $staff->id,
                    'staff_name'       => $staff->name,
                    'staff_email'      => $staff->email,
                    'event_id'         => $event->id,
                    'event_title'      => $event->title,
                    'event_date'       => $event->event_date->toDateString(),
                    'invitation_token' => $token,
                    // TODO: replace with actual RSVP URL helper once routes are defined.
                    'rsvp_url'         => url("/rsvp/{$token}"),
                ]);
            }

            return $payloads;
        });
    }
}
