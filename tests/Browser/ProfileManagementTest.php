<?php

namespace Tests\Browser;

use Tests\DuskTestCase;
use App\Models\User;
use App\Models\TravelRequest;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class ProfileManagementTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * Test complete profile update journey
     */
    public function test_user_can_update_profile_completely()
    {
        $user = User::factory()->create([
            'name' => 'Original Name',
            'email' => 'original@example.com'
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/profile/edit')
                    ->assertSee('Profile Information')
                    ->assertInputValue('name', 'Original Name')
                    ->assertInputValue('email', 'original@example.com')

                    // Update profile information
                    ->clear('name')
                    ->type('name', 'Updated Name')
                    ->clear('email')
                    ->type('email', 'updated@example.com')
                    ->type('nip', '123456789')
                    ->type('jabatan', 'Updated Position')
                    ->type('unit_kerja', 'Updated Unit')

                    // Submit form (should be AJAX)
                    ->press('Update Profile')
                    ->waitFor('#profile-status', 5)
                    ->assertSee('Profile berhasil diperbarui')

                    // Verify navbar updates
                    ->waitUntilMissing('.loading', 3)
                    ->assertDontSee('Original Name'); // Name should be hidden in navbar
        });

        // Verify database changes
        $user->refresh();
        $this->assertEquals('Updated Name', $user->name);
        $this->assertEquals('updated@example.com', $user->email);
    }

    /**
     * Test password update functionality
     */
    public function test_user_can_update_password()
    {
        $user = User::factory()->create([
            'password' => bcrypt('oldpassword')
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/profile/edit')
                    ->scrollIntoView('#password-form')

                    // Fill password form
                    ->type('current_password', 'oldpassword')
                    ->type('password', 'newpassword123')
                    ->type('password_confirmation', 'newpassword123')

                    // Submit password form
                    ->press('Update Password')
                    ->waitFor('#password-status', 5)
                    ->assertSee('Password berhasil diperbarui');
        });
    }

    /**
     * Test profile photo upload with navbar auto-update
     */
    public function test_user_can_upload_profile_photo()
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/profile/edit')

                    // Check current state (no avatar)
                    ->assertMissing('#profile-photo-preview[src*="storage"]')

                    // Upload photo (simulate file upload)
                    ->attach('avatar', __DIR__.'/test-files/avatar.jpg')

                    // Wait for preview to update
                    ->waitFor('#profile-photo-preview', 3)

                    // Submit form
                    ->press('Update Profile')
                    ->waitFor('#profile-status', 5)
                    ->assertSee('Profile berhasil diperbarui')

                    // Check if navbar photo updated
                    ->waitFor('#navbar-profile-photo[src*="storage"]', 5);
        });
    }

    /**
     * Test form validation
     */
    public function test_profile_form_validation_works()
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/profile/edit')

                    // Clear required fields
                    ->clear('name')
                    ->clear('email')

                    // Submit form
                    ->press('Update Profile')
                    ->waitFor('#profile-error', 5)
                    ->assertSee('Error')

                    // Check that fields are highlighted
                    ->assertAttribute('name', 'class', 'border-red-500')
                    ->assertAttribute('email', 'class', 'border-red-500');
        });
    }

    /**
     * Test mobile responsive design
     */
    public function test_profile_page_works_on_mobile()
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->resize(375, 812) // iPhone X dimensions
                    ->loginAs($user)
                    ->visit('/profile/edit')
                    ->assertSee('Profile Information')

                    // Check mobile navbar
                    ->assertVisible('.mobile-menu-button')
                    ->assertMissing('.desktop-menu')

                    // Test form on mobile
                    ->type('name', 'Mobile Updated Name')
                    ->press('Update Profile')
                    ->waitFor('#profile-status', 5)
                    ->assertSee('Profile berhasil diperbarui');
        });
    }

    /**
     * Test accessibility features
     */
    public function test_profile_page_accessibility()
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/profile/edit')

                    // Check for proper labels
                    ->assertAttribute('input[name="name"]', 'aria-label')
                    ->assertAttribute('input[name="email"]', 'aria-label')

                    // Check for ARIA attributes
                    ->assertAttribute('form', 'role')

                    // Check focus management
                    ->keys('input[name="name"]', ['{tab}'])
                    ->assertFocused('input[name="email"]');
        });
    }

    /**
     * Test cross-tab communication for profile updates
     */
    public function test_profile_updates_sync_across_tabs()
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser1, Browser $browser2) use ($user) {
            // Open profile page in two tabs
            $browser1->loginAs($user)->visit('/profile/edit');
            $browser2->loginAs($user)->visit('/dashboard');

            // Update profile in first tab
            $browser1->type('name', 'Cross Tab Updated Name')
                     ->press('Update Profile')
                     ->waitFor('#profile-status', 5);

            // Check if second tab updates
            $browser2->waitForText('Cross Tab Updated Name', 10);
        });
    }

    /**
     * Test error handling and recovery
     */
    public function test_profile_handles_errors_gracefully()
    {
        $user = User::factory()->create();
        $existingUser = User::factory()->create(['email' => 'taken@example.com']);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/profile/edit')

                    // Try to use existing email
                    ->type('email', 'taken@example.com')
                    ->press('Update Profile')
                    ->waitFor('#profile-error', 5)
                    ->assertSee('Error')

                    // Fix the error
                    ->type('email', 'new-unique@example.com')
                    ->press('Update Profile')
                    ->waitFor('#profile-status', 5)
                    ->assertSee('Profile berhasil diperbarui');
        });
    }
}
