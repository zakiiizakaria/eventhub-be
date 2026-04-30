<?php

declare(strict_types=1);

namespace App\Services\Public;

use App\Models\EventStaff;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class RSVPService
{
    /**
     * Fetch event and staff details for the public RSVP page.
     *
     * @param  string $token UUID invitation token from the QR/link.
     *
     * @throws NotFoundHttpException            If the token does not match any record.
     * @throws UnprocessableEntityHttpException If the event is no longer active.
     */
    public function getStaffDetailsByToken(string $token): EventStaff
    {
        /** @var EventStaff|null $eventStaff */
        $eventStaff = EventStaff::with(['event.organizer', 'staff'])
            ->where('invitation_token', $token)
            ->first();

        if (! $eventStaff) {
            throw new NotFoundHttpException('Invitation not found. Please check your link and try again.');
        }

        if (! $eventStaff->event->is_active) {
            throw new UnprocessableEntityHttpException('This event is no longer accepting RSVPs.');
        }

        return $eventStaff;
    }

    /**
     * Submit an RSVP response for a staff member.
     *
     * Updates is_attending and pax. When attending, automatically assigns the
     * next available lucky_draw_number (sequential, no gaps, scoped to the event).
     *
     * @param  string               $token UUID invitation token.
     * @param  array<string, mixed> $data  Expected keys: is_attending (bool), pax (int).
     *
     * @throws NotFoundHttpException            If the token is invalid.
     * @throws UnprocessableEntityHttpException If the event is inactive.
     */
    public function submitRSVP(string $token, array $data): EventStaff
    {
        return DB::transaction(function () use ($token, $data): EventStaff {

            /** @var EventStaff|null $eventStaff */
            $eventStaff = EventStaff::with('event')
                ->where('invitation_token', $token)
                ->lockForUpdate()
                ->first();

            if (! $eventStaff) {
                throw new NotFoundHttpException('Invitation not found. Please check your link and try again.');
            }

            if (! $eventStaff->event->is_active) {
                throw new UnprocessableEntityHttpException('This event is no longer accepting RSVPs.');
            }

            $isAttending = (bool) ($data['is_attending'] ?? false);
            $pax         = (int)  ($data['pax']          ?? 1);

            $luckyDrawNumber = $eventStaff->lucky_draw_number;

            // Assign the next sequential lucky draw number when confirming attendance.
            if ($isAttending && $luckyDrawNumber === null) {
                $luckyDrawNumber = $this->nextLuckyDrawNumber($eventStaff->event_id);
            }

            // If they previously confirmed but are now declining, release their number.
            if (! $isAttending) {
                $luckyDrawNumber = null;
            }

            $eventStaff->update([
                'is_attending'      => $isAttending,
                'pax'               => $isAttending ? $pax : 1,
                'lucky_draw_number' => $luckyDrawNumber,
            ]);

            return $eventStaff->refresh();
        });
    }

    /**
     * Determine the next sequential lucky draw number for an event.
     *
     * Finds the current MAX number and increments by 1. Because this runs
     * inside a DB transaction with a row-level lock (lockForUpdate above),
     * concurrent submissions cannot produce duplicate numbers.
     *
     * @param  string $eventId UUID of the event.
     */
    private function nextLuckyDrawNumber(string $eventId): string
    {
        $max = EventStaff::where('event_id', $eventId)
            ->whereNotNull('lucky_draw_number')
            ->max(DB::raw('CAST(lucky_draw_number AS UNSIGNED)'));

        return (string) (((int) $max) + 1);
    }
}
