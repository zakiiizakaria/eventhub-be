<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Admin\ScanAttendanceRequest;
use App\Services\Admin\AttendanceService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Throwable;

class AttendanceController extends BaseController
{
    public function __construct(
        private readonly AttendanceService $attendanceService,
    ) {}

    /**
     * Process a QR code scan to record staff check-in.
     */
    public function scan(ScanAttendanceRequest $request): JsonResponse
    {
        try {
            $attendance = $this->attendanceService->scanQRCode(
                eventId        : $request->validated('event_id'),
                invitationToken: $request->validated('invitation_token'),
                adminId        : $request->user()->id,
            );

            $attendance->load(['staff', 'event']);

            return $this->success($attendance, 'Check-in recorded successfully.', 201);

        } catch (NotFoundHttpException $e) {
            return $this->error($e->getMessage(), 404);
        } catch (UnprocessableEntityHttpException $e) {
            return $this->error($e->getMessage(), 422);
        } catch (Throwable $e) {
            return $this->error('An unexpected error occurred during check-in.', 500);
        }
    }
}
