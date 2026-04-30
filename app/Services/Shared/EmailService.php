<?php

declare(strict_types=1);

namespace App\Services\Shared;

use App\Mail\EventInvitationMail;
use App\Models\Event;
use App\Models\EventStaff;
use App\Models\Staff;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class EmailService
{
    /**
     * Create event_staff records for a batch of staff members, dispatch
     * queued invitation emails, and return the payload summary.
     *
     * - Generates a unique UUID invitation_token per staff member.
     * - Skips staff members who are already enrolled in the event (idempotent).
     * - Queues an EventInvitationMail for each newly enrolled staff member.
     *
     * @param  string        $eventId  UUID of the event.
     * @param  array<string> $staffIds Array of staff UUIDs to invite.
     *
     * @return Collection<int, array<string, mixed>> Payload summaries for the API response.
     *
     * @throws NotFoundHttpException            If the event does not exist.
     * @throws UnprocessableEntityHttpException If the event is inactive.
     */
    public function sendBulkInvitation(string $eventId, array $staffIds): Collection
    {
        return DB::transaction(function () use ($eventId, $staffIds): Collection {

            // 1. Validate event exists and is active.
            /** @var Event|null $event */
            $event = Event::with('organizer')->find($eventId);

            if (! $event) {
                throw new NotFoundHttpException('Event not found.');
            }

            if (! $event->is_active) {
                throw new UnprocessableEntityHttpException('Cannot send invitations for an inactive event.');
            }

            // 2. Fetch only staff that belong to the same organizer.
            $staffMembers = Staff::whereIn('id', $staffIds)
                ->where('organizer_id', $event->organizer_id)
                ->get()
                ->keyBy('id');

            // 3. Determine already-enrolled staff to avoid duplicate records.
            $alreadyEnrolled = EventStaff::where('event_id', $eventId)
                ->whereIn('staff_id', $staffIds)
                ->pluck('staff_id')
                ->flip(); // O(1) lookup

            $frontendBase = rtrim(config('app.frontend_url', 'http://localhost:3000'), '/');
            $payloads     = collect();

            foreach ($staffMembers as $staff) {
                if ($alreadyEnrolled->has($staff->id)) {
                    continue; // Already enrolled — skip silently.
                }

                $token = (string) Str::uuid();

                /** @var EventStaff $eventStaff */
                $eventStaff = EventStaff::create([
                    'event_id'         => $eventId,
                    'staff_id'         => $staff->id,
                    'invitation_token' => $token,
                    'is_attending'     => false,
                    'pax'              => 1,
                ]);

                // Set relationships in-memory so the Mailable doesn't re-query.
                $eventStaff->setRelation('event', $event);
                $eventStaff->setRelation('staff', $staff);

                // Dispatch invitation email to the queue.
                Mail::to($staff->email)->queue(new EventInvitationMail($eventStaff));

                $payloads->push([
                    'staff_id'         => $staff->id,
                    'staff_name'       => $staff->name,
                    'staff_email'      => $staff->email,
                    'event_id'         => $event->id,
                    'event_title'      => $event->title,
                    'event_date'       => $event->event_date->toDateString(),
                    'invitation_token' => $token,
                    'rsvp_url'         => "{$frontendBase}/rsvp/{$token}",
                ]);
            }

            return $payloads;
        });
    }
}
