<?php

namespace Tests\Feature;

use App\Models\CoralColony;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CoralColonyApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test a: Akses endpoint /api/coral-colonies tanpa token menghasilkan status 401.
     */
    public function test_access_coral_colonies_without_token_returns_401(): void
    {
        $response = $this->getJson('/api/coral-colonies');

        $response->assertStatus(401);
    }

    /**
     * Test b: Login berhasil lalu membuat data koloni karang baru menghasilkan status 201,
     * dan gagal validasi saat tambah data menghasilkan 422.
     */
    public function test_login_and_create_coral_colony_returns_201_and_validation_failure_returns_422(): void
    {
        // 1. Setup user dan test login
        $user = User::factory()->create([
            'email' => 'peneliti@coraltracker.id',
            'password' => bcrypt('password123'),
        ]);

        $loginResponse = $this->postJson('/api/login', [
            'email' => 'peneliti@coraltracker.id',
            'password' => 'password123',
        ]);

        $loginResponse->assertStatus(200)
            ->assertJsonStructure(['token', 'token_type', 'user']);

        $token = $loginResponse->json('token');

        // 2. Test tambah data koloni karang baru menghasilkan status 201
        $payload = [
            'kode_fragmen' => 'CRL-2026-TEST',
            'spesies_ilmiah' => 'Acropora cervicornis',
            'zona_terumbu' => 'Sektor Utara - Karimunjawa',
            'kedalaman_meter' => 8.5,
            'persentase_hidup' => 95,
            'status_kesehatan' => 'Sehat',
            'catatan_observasi' => 'Pengujian koloni aktif.',
        ];

        $createResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/coral-colonies', $payload);

        $createResponse->assertStatus(201)
            ->assertJsonFragment([
                'kode_fragmen' => 'CRL-2026-TEST',
                'spesies_ilmiah' => 'Acropora cervicornis',
            ]);

        $this->assertDatabaseHas('coral_colonies', [
            'kode_fragmen' => 'CRL-2026-TEST',
        ]);

        // 3. Test gagal validasi menghasilkan status 422
        $invalidPayload = [
            'kode_fragmen' => '', // kosong, melanggar validasi required
            'persentase_hidup' => 150, // di luar rentang 0-100
        ];

        $invalidResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/coral-colonies', $invalidPayload);

        $invalidResponse->assertStatus(422)
            ->assertJsonValidationErrors(['kode_fragmen', 'spesies_ilmiah', 'zona_terumbu', 'kedalaman_meter', 'persentase_hidup', 'status_kesehatan']);
    }

    /**
     * Test c: Mengambil daftar koloni karang dengan token menghasilkan status 200.
     */
    public function test_can_fetch_coral_colonies_list(): void
    {
        $user = User::factory()->create();
        CoralColony::factory()->count(3)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/coral-colonies');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    /**
     * Test d: Update data koloni karang menghasilkan status 200.
     */
    public function test_can_update_coral_colony(): void
    {
        $user = User::factory()->create();
        $colony = CoralColony::factory()->create([
            'status_kesehatan' => 'Pemutihan/Bleaching',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->putJson('/api/coral-colonies/' . $colony->id, [
                'status_kesehatan' => 'Pemulihan',
                'persentase_hidup' => 75,
            ]);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'status_kesehatan' => 'Pemulihan',
                'persentase_hidup' => 75,
            ]);

        $this->assertDatabaseHas('coral_colonies', [
            'id' => $colony->id,
            'status_kesehatan' => 'Pemulihan',
        ]);
    }

    /**
     * Test e: Hapus data koloni karang menghasilkan status 200.
     */
    public function test_can_delete_coral_colony(): void
    {
        $user = User::factory()->create();
        $colony = CoralColony::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson('/api/coral-colonies/' . $colony->id);

        $response->assertStatus(200);

        $this->assertDatabaseMissing('coral_colonies', [
            'id' => $colony->id,
        ]);
    }

    /**
     * Test f: Logout berhasil menghapus token.
     */
    public function test_logout_revokes_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/logout');

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => 'Logged out successfully.']);

        $this->assertCount(0, $user->fresh()->tokens);
    }
}
