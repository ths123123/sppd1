<?php

namespace Tests\Feature\UIComponents;

use Tests\TestCase;
use App\Models\User;
use App\Models\TravelRequest;
use App\Models\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class UIComponentsTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $admin;
    protected $kasubbag;
    protected $regularUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->kasubbag = User::factory()->create(['role' => 'kasubbag']);
        $this->regularUser = User::factory()->create(['role' => 'user']);
    }

    /** @test */
    public function navbar_is_properly_displayed()
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('dashboard'));
        $response->assertStatus(200);

        // Check navbar components
        $response->assertSee('KPU Kabupaten Cirebon');
        $response->assertSee('Dashboard');
        $response->assertSee('SPPD');
        $response->assertSee('Analytics & Laporan');
        $response->assertSee('Dokumen');
        $response->assertSee('Kelola User');
    }

    /** @test */
    public function mobile_menu_is_accessible()
    {
        $this->actingAs($this->regularUser);

        $response = $this->get(route('dashboard'));
        $response->assertStatus(200);

        // Check mobile menu components
        $response->assertSee('mobile-menu');
        $response->assertSee('mobile-offcanvas');
        $response->assertSee('mobile-menu-header');
        $response->assertSee('mobile-menu-list');
    }

    /** @test */
    public function mobile_menu_shows_correct_items()
    {
        $this->actingAs($this->kasubbag);

        $response = $this->get(route('dashboard'));
        $response->assertStatus(200);

        // Check mobile menu items
        $response->assertSee('MENU UTAMA');
        $response->assertSee('Dashboard');
        $response->assertSee('SPPD');
        $response->assertSee('Analytics & Laporan');
        $response->assertSee('Dokumen');
    }

    /** @test */
    public function mobile_menu_expandable_sections_work()
    {
        $this->actingAs($this->kasubbag);

        $response = $this->get(route('dashboard'));
        $response->assertStatus(200);

        // Check expandable sections
        $response->assertSee('sppdOpen');
        $response->assertSee('analyticsOpen');
        $response->assertSee('dokumenOpen');
        $response->assertSee('closeAll');
    }

    /** @test */
    public function notification_bell_is_displayed()
    {
        $this->actingAs($this->regularUser);

        $response = $this->get(route('dashboard'));
        $response->assertStatus(200);

        // Check notification components
        $response->assertSee('notification-bell');
        $response->assertSee('notification-badge');
        $response->assertSee('fas fa-bell');
    }

    /** @test */
    public function notification_dropdown_works()
    {
        $this->actingAs($this->regularUser);

        $response = $this->get(route('dashboard'));
        $response->assertStatus(200);

        // Check notification dropdown
        $response->assertSee('notificationDropdown');
        $response->assertSee('notification-item');
    }

    /** @test */
    public function user_profile_dropdown_is_displayed()
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('dashboard'));
        $response->assertStatus(200);

        // Check profile dropdown
        $response->assertSee('profile-dropdown');
        $response->assertSee('Profil Saya');
        $response->assertSee('Setting');
        $response->assertSee('Logout');
    }

    /** @test */
    public function responsive_design_components_are_present()
    {
        $this->actingAs($this->regularUser);

        $response = $this->get(route('dashboard'));
        $response->assertStatus(200);

        // Check responsive design classes
        $response->assertSee('sm:hidden'); // Mobile only
        $response->assertSee('hidden sm:flex'); // Desktop only
        $response->assertSee('lg:space-x-3'); // Large screen spacing
        $response->assertSee('xl:block'); // Extra large screen
    }

    /** @test */
    public function navigation_dropdowns_work_properly()
    {
        $this->actingAs($this->kasubbag);

        $response = $this->get(route('dashboard'));
        $response->assertStatus(200);

        // Check SPPD dropdown
        $response->assertSee('sppdDropdown');
        $response->assertSee('Buat SPPD');
        $response->assertSee('SPPD Saya');
        $response->assertSee('Daftar SPPD');

        // Check Analytics dropdown
        $response->assertSee('analyticsDropdown');
        $response->assertSee('Analytics');
        $response->assertSee('Laporan');

        // Check Dokumen dropdown
        $response->assertSee('dokumenDropdown');
        $response->assertSee('Dokumen Saya');
        $response->assertSee('Manajemen Template');
    }

    /** @test */
    public function role_based_navigation_is_correct()
    {
        // Test admin navigation
        $this->actingAs($this->admin);
        $response = $this->get(route('dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Kelola User');
        $response->assertSee('Manajemen Template');

        // Test kasubbag navigation
        $this->actingAs($this->kasubbag);
        $response = $this->get(route('dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Buat SPPD');
        $response->assertSee('Analytics & Laporan');

        // Test regular user navigation
        $this->actingAs($this->regularUser);
        $response = $this->get(route('dashboard'));
        $response->assertStatus(200);
        $response->assertDontSee('Kelola User');
        $response->assertDontSee('Manajemen Template');
    }

    /** @test */
    public function logo_component_is_displayed()
    {
        $this->actingAs($this->regularUser);

        $response = $this->get(route('dashboard'));
        $response->assertStatus(200);

        // Check logo component
        $response->assertSee('logo.png');
        $response->assertSee('KPU Kabupaten Cirebon');
        $response->assertSee('w-12 h-12'); // Logo dimensions
    }

    /** @test */
    public function breadcrumb_navigation_is_present()
    {
        $this->actingAs($this->kasubbag);

        $response = $this->get(route('travel-requests.create'));
        $response->assertStatus(200);

        // Check breadcrumb navigation
        $response->assertSee('breadcrumb');
        $response->assertSee('Dashboard');
        $response->assertSee('SPPD');
        $response->assertSee('Buat SPPD');
    }

    /** @test */
    public function form_components_are_properly_styled()
    {
        $this->actingAs($this->kasubbag);

        $response = $this->get(route('travel-requests.create'));
        $response->assertStatus(200);

        // Check form styling
        $response->assertSee('form-input');
        $response->assertSee('form-select');
        $response->assertSee('form-textarea');
        $response->assertSee('btn-primary');
        $response->assertSee('btn-secondary');
    }

    /** @test */
    public function table_components_are_responsive()
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('travel-requests.index'));
        $response->assertStatus(200);

        // Check responsive table
        $response->assertSee('table-responsive');
        $response->assertSee('overflow-x-auto');
        $response->assertSee('min-w-full');
    }

    /** @test */
    public function card_components_are_properly_displayed()
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('dashboard'));
        $response->assertStatus(200);

        // Check card components
        $response->assertSee('card');
        $response->assertSee('card-header');
        $response->assertSee('card-body');
        $response->assertSee('card-footer');
    }

    /** @test */
    public function modal_components_are_functional()
    {
        $this->actingAs($this->kasubbag);

        $response = $this->get(route('travel-requests.create'));
        $response->assertStatus(200);

        // Check modal components
        $response->assertSee('modal');
        $response->assertSee('modal-backdrop');
        $response->assertSee('modal-dialog');
        $response->assertSee('modal-content');
    }

    /** @test */
    public function alert_components_are_displayed()
    {
        $this->actingAs($this->regularUser);

        $response = $this->get(route('dashboard'));
        $response->assertStatus(200);

        // Check alert components
        $response->assertSee('alert');
        $response->assertSee('alert-success');
        $response->assertSee('alert-warning');
        $response->assertSee('alert-error');
    }

    /** @test */
    public function button_components_have_proper_styling()
    {
        $this->actingAs($this->kasubbag);

        $response = $this->get(route('travel-requests.create'));
        $response->assertStatus(200);

        // Check button styling
        $response->assertSee('btn');
        $response->assertSee('btn-primary');
        $response->assertSee('btn-secondary');
        $response->assertSee('btn-danger');
        $response->assertSee('btn-sm');
        $response->assertSee('btn-lg');
    }

    /** @test */
    public function icon_components_are_loaded()
    {
        $this->actingAs($this->regularUser);

        $response = $this->get(route('dashboard'));
        $response->assertStatus(200);

        // Check FontAwesome icons
        $response->assertSee('fas fa-bell');
        $response->assertSee('fas fa-chevron-down');
        $response->assertSee('fas fa-user');
        $response->assertSee('fas fa-cog');
    }

    /** @test */
    public function loading_states_are_handled()
    {
        $this->actingAs($this->kasubbag);

        $response = $this->get(route('travel-requests.create'));
        $response->assertStatus(200);

        // Check loading states
        $response->assertSee('loading');
        $response->assertSee('spinner');
        $response->assertSee('disabled');
    }

    /** @test */
    public function error_states_are_displayed()
    {
        $this->actingAs($this->kasubbag);

        $response = $this->get(route('travel-requests.create'));
        $response->assertStatus(200);

        // Check error handling
        $response->assertSee('error');
        $response->assertSee('validation-error');
        $response->assertSee('field-error');
    }

    /** @test */
    public function success_states_are_displayed()
    {
        $this->actingAs($this->kasubbag);

        $response = $this->get(route('travel-requests.create'));
        $response->assertStatus(200);

        // Check success handling
        $response->assertSee('success');
        $response->assertSee('success-message');
        $response->assertSee('success-icon');
    }

    /** @test */
    public function pagination_components_are_responsive()
    {
        $this->actingAs($this->admin);

        // Create multiple SPPD for pagination
        TravelRequest::factory()->count(25)->create();

        $response = $this->get(route('travel-requests.index'));
        $response->assertStatus(200);

        // Check pagination
        $response->assertSee('pagination');
        $response->assertSee('page-link');
        $response->assertSee('page-item');
    }

    /** @test */
    public function search_components_are_functional()
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('travel-requests.index'));
        $response->assertStatus(200);

        // Check search components
        $response->assertSee('search');
        $response->assertSee('search-input');
        $response->assertSee('search-button');
        $response->assertSee('filter');
    }

    /** @test */
    public function filter_components_work_properly()
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('travel-requests.index'));
        $response->assertStatus(200);

        // Check filter components
        $response->assertSee('filter');
        $response->assertSee('filter-dropdown');
        $response->assertSee('filter-option');
        $response->assertSee('filter-reset');
    }

    /** @test */
    public function sort_components_are_displayed()
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('travel-requests.index'));
        $response->assertStatus(200);

        // Check sorting components
        $response->assertSee('sort');
        $response->assertSee('sort-asc');
        $response->assertSee('sort-desc');
        $response->assertSee('sortable');
    }

    /** @test */
    public function tooltip_components_are_functional()
    {
        $this->actingAs($this->kasubbag);

        $response = $this->get(route('travel-requests.create'));
        $response->assertStatus(200);

        // Check tooltip components
        $response->assertSee('tooltip');
        $response->assertSee('tooltip-text');
        $response->assertSee('data-tooltip');
    }

    /** @test */
    public function progress_components_are_displayed()
    {
        $this->actingAs($this->kasubbag);

        $response = $this->get(route('travel-requests.create'));
        $response->assertStatus(200);

        // Check progress components
        $response->assertSee('progress');
        $response->assertSee('progress-bar');
        $response->assertSee('progress-step');
    }

    /** @test */
    public function tab_components_work_properly()
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('settings.index'));
        $response->assertStatus(200);

        // Check tab components
        $response->assertSee('tab');
        $response->assertSee('tab-content');
        $response->assertSee('tab-pane');
        $response->assertSee('tab-nav');
    }

    /** @test */
    public function accordion_components_are_functional()
    {
        $this->actingAs($this->kasubbag);

        $response = $this->get(route('travel-requests.create'));
        $response->assertStatus(200);

        // Check accordion components
        $response->assertSee('accordion');
        $response->assertSee('accordion-item');
        $response->assertSee('accordion-header');
        $response->assertSee('accordion-body');
    }

    /** @test */
    public function sidebar_components_are_responsive()
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('dashboard'));
        $response->assertStatus(200);

        // Check sidebar components
        $response->assertSee('sidebar');
        $response->assertSee('sidebar-toggle');
        $response->assertSee('sidebar-collapse');
    }

    /** @test */
    public function footer_components_are_displayed()
    {
        $this->actingAs($this->regularUser);

        $response = $this->get(route('dashboard'));
        $response->assertStatus(200);

        // Check footer components
        $response->assertSee('footer');
        $response->assertSee('footer-content');
        $response->assertSee('footer-links');
    }

    /** @test */
    public function accessibility_features_are_implemented()
    {
        $this->actingAs($this->regularUser);

        $response = $this->get(route('dashboard'));
        $response->assertStatus(200);

        // Check accessibility features
        $response->assertSee('aria-label');
        $response->assertSee('aria-expanded');
        $response->assertSee('aria-hidden');
        $response->assertSee('role');
    }

    /** @test */
    public function keyboard_navigation_works()
    {
        $this->actingAs($this->kasubbag);

        $response = $this->get(route('travel-requests.create'));
        $response->assertStatus(200);

        // Check keyboard navigation
        $response->assertSee('tabindex');
        $response->assertSee('onkeydown');
        $response->assertSee('onkeyup');
    }

    /** @test */
    public function focus_management_is_proper()
    {
        $this->actingAs($this->kasubbag);

        $response = $this->get(route('travel-requests.create'));
        $response->assertStatus(200);

        // Check focus management
        $response->assertSee('focus');
        $response->assertSee('focus-visible');
        $response->assertSee('focus-within');
    }

    /** @test */
    public function color_scheme_is_consistent()
    {
        $this->actingAs($this->regularUser);

        $response = $this->get(route('dashboard'));
        $response->assertStatus(200);

        // Check color scheme consistency
        $response->assertSee('text-white');
        $response->assertSee('bg-red-600');
        $response->assertSee('text-red-800');
        $response->assertSee('hover:text-gray-200');
    }

    /** @test */
    public function typography_is_properly_scaled()
    {
        $this->actingAs($this->regularUser);

        $response = $this->get(route('dashboard'));
        $response->assertStatus(200);

        // Check typography scaling
        $response->assertSee('text-sm');
        $response->assertSee('text-base');
        $response->assertSee('text-lg');
        $response->assertSee('font-medium');
        $response->assertSee('font-bold');
    }

    /** @test */
    public function spacing_is_consistent()
    {
        $this->actingAs($this->regularUser);

        $response = $this->get(route('dashboard'));
        $response->assertStatus(200);

        // Check spacing consistency
        $response->assertSee('px-4');
        $response->assertSee('py-2');
        $response->assertSee('space-x-2');
        $response->assertSee('space-y-4');
        $response->assertSee('gap-3');
    }

    /** @test */
    public function animations_are_smooth()
    {
        $this->actingAs($this->regularUser);

        $response = $this->get(route('dashboard'));
        $response->assertStatus(200);

        // Check animation classes
        $response->assertSee('transition');
        $response->assertSee('duration-200');
        $response->assertSee('ease-in-out');
        $response->assertSee('transform');
        $response->assertSee('hover:scale-105');
    }

    /** @test */
    public function responsive_breakpoints_are_proper()
    {
        $this->actingAs($this->regularUser);

        $response = $this->get(route('dashboard'));
        $response->assertStatus(200);

        // Check responsive breakpoints
        $response->assertSee('sm:hidden');
        $response->assertSee('md:block');
        $response->assertSee('lg:flex');
        $response->assertSee('xl:grid');
        $response->assertSee('2xl:container');
    }
}
