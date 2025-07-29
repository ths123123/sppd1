<?php

use Tests\TestCase;
use App\Models\User;
use App\Models\TravelRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(TestCase::class);

describe('TravelRequest Model', function () {

    test('it belongs to a user', function () {
        // Arrange
        $user = User::factory()->create();
        $travelRequest = TravelRequest::factory()->create(['user_id' => $user->id]);

        // Act & Assert
        expect($travelRequest->user)->toBeInstanceOf(User::class);
        expect($travelRequest->user->id)->toBe($user->id);
    });

    test('it has status scope', function () {
        $user = User::factory()->create(['nip' => '198402132009121005']);
        $travelRequest = TravelRequest::factory()->create([
            'user_id' => $user->id,
            'status' => 'in_review', // valid enum
        ]);
        expect($travelRequest->status)->toBe('in_review');
    });

    test('it calculates duration correctly', function () {
        // Arrange
        $travelRequest = TravelRequest::factory()->create([
            'tanggal_berangkat' => '2025-07-01',
            'tanggal_kembali' => '2025-07-03'
        ]);

        // Act
        $duration = $travelRequest->calculateDuration();

        // Assert
        expect($duration)->toBe(3);
    });

    test('it generates correct total budget', function () {
        // Arrange
        $travelRequest = TravelRequest::factory()->create([
            'biaya_transport' => 1500000,
            'biaya_penginapan' => 600000,
            'uang_harian' => 450000,
            'biaya_lainnya' => 100000
        ]);

        // Act
        $total = $travelRequest->total_biaya;

        // Assert
        expect($total)->toBe(2650000);
    });

    test('it has approval relationship', function () {
        // Arrange
        $travelRequest = TravelRequest::factory()->create();

        // Act & Assert
        expect($travelRequest->approvals())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class);
    });

    test('it can be filtered by date range', function () {
        // Arrange - Clean up existing data first
        TravelRequest::where('tanggal_berangkat', '>=', '2025-07-01')->delete();
        
        TravelRequest::factory()->create(['tanggal_berangkat' => '2025-07-01']);
        TravelRequest::factory()->create(['tanggal_berangkat' => '2025-07-15']);
        TravelRequest::factory()->create(['tanggal_berangkat' => '2025-08-01']);

        // Act
        $julyRequests = TravelRequest::whereBetween('tanggal_berangkat', ['2025-07-01', '2025-07-31'])->get();

        // Assert
        expect($julyRequests)->toHaveCount(2);
    });

    test('it formats dates correctly', function () {
        // Arrange
        $travelRequest = TravelRequest::factory()->create([
            'tanggal_berangkat' => '2025-07-01'
        ]);

        // Act
        $formattedDate = $travelRequest->tanggal_berangkat->format('d-m-Y');

        // Assert
        expect($formattedDate)->toBe('01-07-2025');
    });

    test('it has correct fillable attributes', function () {
        // Arrange
        $fillable = [
            'kode_sppd', 'user_id', 'tempat_berangkat', 'tujuan', 'keperluan',
            'tanggal_berangkat', 'tanggal_kembali', 'lama_perjalanan',
            'transportasi', 'tempat_menginap', 'biaya_transport',
            'biaya_penginapan', 'uang_harian', 'biaya_lainnya',
            'total_biaya', 'sumber_dana', 'status', 'current_approval_level',
            'approval_sequence', 'approval_history', 'catatan_pemohon',
            'catatan_approval', 'is_urgent', 'nomor_surat_tugas',
            'tanggal_surat_tugas', 'submitted_at', 'approved_at'
        ];

        // Act
        $model = new TravelRequest();

        // Assert
        expect($model->getFillable())->toEqual($fillable);
    });
});
