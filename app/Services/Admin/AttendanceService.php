<?php

declare(strict_types=1);

namespace App\Services\Admin;

use App\Models\Attendance;
use App\Models\EventStaff;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class AttendanceService
{
    /**
     * Process a QR-code scan for event check-in.
     *
     * @param  string $eventId         UUID of the event being scanned at.
     * @param  string $invitationToken UUID invitation token on the QR code.
     * @param  string $adminId         UUID of the admin performing the scan.
     *
     * @throws NotFoundHttpException              If the token is invalid or does not belong to this event.
     * @throws UnprocessableEntityHttpException   If the event is inactive or staff already checked in.
     */
    public function scanQRCode(string $eventId, string $invitationToken, string $adminId): Attendance
    {
        return DB::transaction(function () use ($eventId, $invitationToken, $adminId): Attendance {

            // 1. Resolve the event-staff record from the invitation token.
            /** @var EventStaff|null $eventStaff */
            $eventStaff = EventStaff::with('event')
                ->where('invitation_token', $invitationToken)
                ->lockForUpdate()
                ->first();

            if (! $eventStaff) {
                throw new NotFoundHttpException('Invalid or unrecognised invitation token.');
            }

            // 2. Ensure the token belongs to the correct event.
            if ($eventStaff->event_id !== $eventId) {
                throw new NotFoundHttpException('This invitation token does not belong to the specified event.');
            }

            // 3. Guard: event must be active.
            if (! $eventStaff->event->is_active) {
                throw new UnprocessableEntityHttpException('This event is no longer active.');
            }

            // 4. Guard: prevent duplicate check-ins for the same staff member in the same event.
            $alreadyCheckedIn = Attendance::where('event_id', $eventId)
                ->where('staff_id', $eventStaff->staff_id)
                ->exists();

            if ($alreadyCheckedIn) {
                throw new UnprocessableEntityHttpException('This staff member has already checked in for this event.');
            }

            // 5. Record attendance.
            return Attendance::create([
                'event_id'   => $eventId,
                'staff_id'   => $eventStaff->staff_id,
                'scanned_at' => now(),
                'scanned_by' => $adminId,
            ]);
        });
    }
}
