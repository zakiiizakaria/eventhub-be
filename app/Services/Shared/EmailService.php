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
     * Invite a batch of staff members to an event.
     *
     * Behaviour:
     * - **New staff**: creates an event_staff record with a fresh UUID token, then queues the email.
     * - **Already-enrolled staff**: re-uses their existing token and re-queues the email (resend).
     * - Staff not belonging to the same organizer are silently ignored.
     *
     * @param  string        $eventId  UUID of the event.
     * @param  array<string> $staffIds Array of staff UUIDs to invite / resend to.
     *
     * @return array{invited: Collection, resent: Collection}
     *
     * @throws NotFoundHttpException            If the event does not exist.
     * @throws UnprocessableEntityHttpException If the event is inactive.
     */
    public function sendBulkInvitation(string $eventId, array $staffIds): array
    {
        return DB::transaction(function () use ($eventId, $staffIds): array {

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

            // 3. Fetch already-enrolled records keyed by staff_id for O(1) lookup.
            $enrolledRecords = EventStaff::where('event_id', $eventId)
                ->whereIn('staff_id', $staffIds)
                ->get()
                ->keyBy('staff_id');

            $frontendBase = rtrim(config('app.frontend_url', 'http://localhost:3000'), '/');
            $newInvites   = collect();
            $resent       = collect();

            foreach ($staffMembers as $staff) {

                if ($enrolledRecords->has($staff->id)) {
                    // ── Resend path: reuse the existing token ──────────────────
                    /** @var EventStaff $eventStaff */
                    $eventStaff = $enrolledRecords->get($staff->id);
                    $eventStaff->setRelation('event', $event);
                    $eventStaff->setRelation('staff', $staff);

                    Mail::to($staff->email)->queue(new EventInvitationMail($eventStaff));

                    $resent->push([
                        'staff_id'         => $staff->id,
                        'staff_name'       => $staff->name,
                        'staff_email'      => $staff->email,
                        'invitation_token' => $eventStaff->invitation_token,
                        'rsvp_url'         => "{$frontendBase}/rsvp/{$eventStaff->invitation_token}",
                    ]);

                } else {
                    // ── New invite path: create record + fresh token ───────────
                    $token = (string) Str::uuid();

                    /** @var EventStaff $eventStaff */
                    $eventStaff = EventStaff::create([
                        'event_id'         => $eventId,
                        'staff_id'         => $staff->id,
                        'invitation_token' => $token,
                        'is_attending'     => false,
                        'pax'              => 1,
                    ]);

                    $eventStaff->setRelation('event', $event);
                    $eventStaff->setRelation('staff', $staff);

                    Mail::to($staff->email)->queue(new EventInvitationMail($eventStaff));

                    $newInvites->push([
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
            }

            return ['invited' => $newInvites, 'resent' => $resent];
        });
    }
}
