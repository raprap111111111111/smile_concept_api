<?php

namespace Tests\Unit;

use App\Domain\PatientProfiles\Actions\UpdatePatientProfileAction;
use App\Domain\PatientProfiles\DTOs\UpdatePatientProfileDTO;
use App\Domain\PatientProfiles\Repositories\PatientProfileRepository;
use App\Domain\PatientProfiles\Services\PatientProfileService;
use App\Models\PatientProfile;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

class UpdatePatientProfileActionTest extends TestCase
{
    // Counts the Mockery expectations as assertions, so these don't report
    // as risky "no assertion" tests.
    use MockeryPHPUnitIntegration;

    public function test_it_clears_an_emergency_contact_that_was_sent_as_null(): void
    {
        $profile = new PatientProfile([
            'emergency_contact_name'  => 'Jane Doe',
            'emergency_contact_phone' => '+639171234567',
        ]);

        $repository = Mockery::mock(PatientProfileRepository::class);
        $repository->shouldReceive('update')
            ->once()
            ->with($profile, [
                'emergency_contact_name'  => null,
                'emergency_contact_phone' => null,
            ])
            ->andReturn($profile);

        $action = new UpdatePatientProfileAction($repository, new PatientProfileService());

        $action->execute($profile, new UpdatePatientProfileDTO(
            providedKeys: ['emergency_contact_name', 'emergency_contact_phone'],
        ));
    }

    public function test_it_leaves_out_fields_the_client_never_sent(): void
    {
        $profile = new PatientProfile(['allergies' => 'Penicillin']);

        $repository = Mockery::mock(PatientProfileRepository::class);
        $repository->shouldReceive('update')
            ->once()
            ->with($profile, ['emergency_contact_name' => 'Jane Doe'])
            ->andReturn($profile);

        $action = new UpdatePatientProfileAction($repository, new PatientProfileService());

        $action->execute($profile, new UpdatePatientProfileDTO(
            emergencyContactName: 'Jane Doe',
            providedKeys: ['emergency_contact_name'],
        ));
    }

    public function test_it_rejects_a_malformed_emergency_phone(): void
    {
        $repository = Mockery::mock(PatientProfileRepository::class);
        $repository->shouldNotReceive('update');

        $action = new UpdatePatientProfileAction($repository, new PatientProfileService());

        $this->expectException(\InvalidArgumentException::class);

        $action->execute(new PatientProfile(), new UpdatePatientProfileDTO(
            emergencyContactPhone: '123',
            providedKeys: ['emergency_contact_phone'],
        ));
    }
}
