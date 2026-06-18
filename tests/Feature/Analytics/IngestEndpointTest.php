<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Queue;

beforeEach(function (): void {
    Queue::fake();
});

it('accepts a valid event batch and returns 202', function (): void {
    $response = $this->postJson('/api/v1/events', [
        'events' => [
            ['type' => 'page_view', 'url' => 'https://example.com/', 'referrer' => null, 'properties' => []],
        ],
    ]);

    $response->assertStatus(202)->assertJson(['accepted' => true]);
});

it('rejects a malformed event at the edge', function (): void {
    $response = $this->postJson('/api/v1/events', [
        'events' => [
            ['type' => 'not_a_real_type', 'url' => 'https://example.com/'],
        ],
    ]);

    $response->assertStatus(422);
});
