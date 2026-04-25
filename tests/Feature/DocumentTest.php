<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('s3');
    }

    public function test_worker_can_list_only_their_documents(): void
    {
        $worker1 = User::factory()->worker()->create();
        $worker2 = User::factory()->worker()->create();

        Document::factory()->create(['user_id' => $worker1->id, 'title' => 'Doc Worker 1']);
        Document::factory()->create(['user_id' => $worker2->id, 'title' => 'Doc Worker 2']);

        $response = $this->actingAs($worker1)
            ->getJson('/api/v1/documents');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Doc Worker 1');
    }

    public function test_admin_can_list_all_documents(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory(2)->worker()->create()->each(function ($u) {
            Document::factory()->create(['user_id' => $u->id]);
        });

        $response = $this->actingAs($admin)
            ->getJson('/api/v1/documents');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_download_logs_audit_and_returns_url(): void
    {
        $worker = User::factory()->worker()->create();
        $document = Document::factory()->create(['user_id' => $worker->id, 'file_path' => 'test.pdf']);

        $response = $this->actingAs($worker)
            ->getJson("/api/v1/documents/{$document->id}/download");

        $response->assertStatus(200)
            ->assertJsonStructure(['download_url']);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $worker->id,
            'document_id' => $document->id,
            'action' => 'downloaded'
        ]);
    }

    public function test_worker_cannot_download_others_documents(): void
    {
        $worker1 = User::factory()->worker()->create();
        $worker2 = User::factory()->worker()->create();
        $document = Document::factory()->create(['user_id' => $worker2->id]);

        $response = $this->actingAs($worker1)
            ->getJson("/api/v1/documents/{$document->id}/download");

        $response->assertStatus(403);
    }
}
