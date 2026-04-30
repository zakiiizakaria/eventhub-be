<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Admin\InviteStaffRequest;
use App\Http\Requests\Admin\StoreStaffRequest;
use App\Models\Staff;
use App\Services\Shared\EmailService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Throwable;

class StaffController extends BaseController
{
    public function __construct(
        private readonly EmailService $emailService,
    ) {}

    /**
     * List all staff in the authenticated admin's organizer master list.
     */
    public function index(Request $request): JsonResponse
    {
        $staff = Staff::where('organizer_id', $request->user()->organizer_id)
            ->latest()
            ->get();

        return $this->success($staff, 'Staff list retrieved successfully.');
    }

    /**
     * Add a new staff member to the organizer's master list.
     * The composite unique index on (organizer_id, email) prevents duplicates.
     */
    public function store(StoreStaffRequest $request): JsonResponse
    {
        $staff = Staff::create([
            ...$request->validated(),
            'organizer_id' => $request->user()->organizer_id,
        ]);

        return $this->success($staff, 'Staff member added successfully.', 201);
    }

    /**
     * Invite a batch of staff members to an event by generating invitation tokens
     * and returning the prepared email payloads.
     */
    public function inviteToEvent(InviteStaffRequest $request): JsonResponse
    {
        try {
            ['invited' => $invited, 'resent' => $resent] = $this->emailService->sendBulkInvitation(
                eventId : $request->validated('event_id'),
                staffIds: $request->validated('staff_ids'),
            );

            $total   = $invited->count() + $resent->count();
            $message = match (true) {
                $invited->isNotEmpty() && $resent->isNotEmpty() => "{$invited->count()} invitation(s) sent and {$resent->count()} resent successfully.",
                $invited->isNotEmpty()                          => "{$invited->count()} invitation(s) sent successfully.",
                $resent->isNotEmpty()                           => "{$resent->count()} invitation(s) resent successfully.",
                default                                         => 'No emails were sent. All selected staff may not belong to this organizer.',
            };

            return $this->success([
                'invited_count' => $invited->count(),
                'resent_count'  => $resent->count(),
                'total_count'   => $total,
                'invited'       => $invited,
                'resent'        => $resent,
            ], $message);

        } catch (NotFoundHttpException $e) {
            return $this->error($e->getMessage(), 404);
        } catch (UnprocessableEntityHttpException $e) {
            return $this->error($e->getMessage(), 422);
        } catch (Throwable $e) {
            return $this->error('An unexpected error occurred while sending invitations.', 500);
        }
    }
}
