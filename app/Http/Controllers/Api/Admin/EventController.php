<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Admin\StoreEventRequest;
use App\Http\Requests\Admin\UpdateEventRequest;
use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EventController extends BaseController
{
    /**
     * List all events belonging to the authenticated admin's organizer.
     */
    public function index(Request $request): JsonResponse
    {
        $events = Event::where('organizer_id', $request->user()->organizer_id)
            ->latest()
            ->get();

        return $this->success($events, 'Events retrieved successfully.');
    }

    /**
     * Create a new event for the authenticated admin's organizer.
     */
    public function store(StoreEventRequest $request): JsonResponse
    {
        $event = Event::create([
            ...$request->validated(),
            'organizer_id' => $request->user()->organizer_id,
            'slug'         => $request->input('slug', Str::slug($request->input('title'))),
        ]);

        return $this->success($event, 'Event created successfully.', 201);
    }

    /**
     * Show a single event scoped to the authenticated admin's organizer.
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $event = Event::where('organizer_id', $request->user()->organizer_id)
            ->findOrFail($id);

        return $this->success($event, 'Event retrieved successfully.');
    }

    /**
     * Update an event scoped to the authenticated admin's organizer.
     */
    public function update(UpdateEventRequest $request, string $id): JsonResponse
    {
        $event = Event::where('organizer_id', $request->user()->organizer_id)
            ->findOrFail($id);

        $event->update($request->validated());

        return $this->success($event, 'Event updated successfully.');
    }

    /**
     * Delete an event scoped to the authenticated admin's organizer.
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $event = Event::where('organizer_id', $request->user()->organizer_id)
            ->findOrFail($id);

        $event->delete();

        return $this->success(null, 'Event deleted successfully.');
    }
}
