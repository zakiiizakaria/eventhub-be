<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Public\RSVPSubmitRequest;
use App\Services\Public\RSVPService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Throwable;

class RSVPController extends BaseController
{
    public function __construct(
        private readonly RSVPService $rsvpService,
    ) {}

    /**
     * Return the event and staff details for the public RSVP page.
     *
     * GET /api/v1/public/rsvp/{token}
     */
    public function show(string $token): JsonResponse
    {
        try {
            $eventStaff = $this->rsvpService->getStaffDetailsByToken($token);

            return $this->success([
                'invitation_token' => $eventStaff->invitation_token,
                'is_attending'     => $eventStaff->is_attending,
                'pax'              => $eventStaff->pax,
                'lucky_draw_number'=> $eventStaff->lucky_draw_number,
                'event'            => [
                    'id'         => $eventStaff->event->id,
                    'title'      => $eventStaff->event->title,
                    'event_date' => $eventStaff->event->event_date,
                    'organizer'  => $eventStaff->event->organizer->name ?? null,
                ],
                'staff'            => [
                    'id'    => $eventStaff->staff->id,
                    'name'  => $eventStaff->staff->name,
                    'email' => $eventStaff->staff->email,
                ],
            ], 'RSVP details retrieved successfully.');

        } catch (NotFoundHttpException $e) {
            return $this->error($e->getMessage(), 404);
        } catch (UnprocessableEntityHttpException $e) {
            return $this->error($e->getMessage(), 422);
        } catch (Throwable $e) {
            return $this->error('An unexpected error occurred.', 500);
        }
    }

    /**
     * Submit the RSVP response for a staff member.
     *
     * POST /api/v1/public/rsvp/{token}
     */
    public function submit(RSVPSubmitRequest $request, string $token): JsonResponse
    {
        try {
            $eventStaff = $this->rsvpService->submitRSVP($token, $request->validated());

            return $this->success([
                'is_attending'      => $eventStaff->is_attending,
                'pax'               => $eventStaff->pax,
                'lucky_draw_number' => $eventStaff->lucky_draw_number,
            ], 'RSVP submitted successfully. Thank you!');

        } catch (NotFoundHttpException $e) {
            return $this->error($e->getMessage(), 404);
        } catch (UnprocessableEntityHttpException $e) {
            return $this->error($e->getMessage(), 422);
        } catch (Throwable $e) {
            return $this->error('An unexpected error occurred while processing your RSVP.', 500);
        }
    }
}
