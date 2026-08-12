<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Category;
use App\Models\Book;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;

class AdminWebTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $customer;
    protected $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'BookVerse Admin',
            'email' => 'admin@bookverse.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'status' => 'active',
            'phone' => '+855 12 345 678',
            'address' => 'Phnom Penh',
        ]);

        $this->customer = User::create([
            'name' => 'John Customer',
            'email' => 'customer@example.com',
            'password' => Hash::make('password'),
            'role' => 'customer',
            'status' => 'active',
        ]);

        $this->category = Category::create([
            'name' => 'Fiction',
            'description' => 'Fiction books',
            'status' => 'active',
        ]);
    }

    /** @test */
    public function unauthenticated_user_cannot_access_admin_dashboard()
    {
        $response = $this->get('/admin/dashboard');
        $response->assertRedirect('/login');
    }

    /** @test */
    public function customer_role_cannot_access_admin_dashboard()
    {
        $response = $this->actingAs($this->customer)->get('/admin/dashboard');
        $response->assertRedirect('/login');
    }

    /** @test */
    public function admin_can_login_successfully()
    {
        $response = $this->post('/login', [
            'email' => 'admin@bookverse.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($this->admin);
    }

    /** @test */
    public function admin_can_view_dashboard_with_stats()
    {
        $response = $this->actingAs($this->admin)->get('/admin/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Overview Dashboard');
        $response->assertSee('Total Books');
        $response->assertSee('Total Customers');
        $response->assertSee('Total Orders');
    }

    /** @test */
    public function admin_can_update_profile()
    {
        $response = $this->actingAs($this->admin)->put('/admin/profile', [
            'name' => 'Updated Admin Name',
            'email' => 'admin@bookverse.com',
            'phone' => '+855 99 888 777',
            'address' => 'Siem Reap',
        ]);

        $response->assertRedirect(route('admin.profile'));
        $this->assertDatabaseHas('users', [
            'id' => $this->admin->id,
            'name' => 'Updated Admin Name',
            'phone' => '+855 99 888 777',
        ]);
    }

    /** @test */
    public function admin_can_change_password()
    {
        $response = $this->actingAs($this->admin)->put('/admin/change-password', [
            'current_password' => 'password',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertRedirect(route('admin.change-password'));
        $this->assertTrue(Hash::check('newpassword123', $this->admin->fresh()->password));
    }

    /** @test */
    public function admin_can_create_category()
    {
        $response = $this->actingAs($this->admin)->post('/admin/categories', [
            'name' => 'Self-Help',
            'description' => 'Personal Growth Books',
            'status' => 'active',
        ]);

        $response->assertRedirect(route('admin.categories.index'));
        $this->assertDatabaseHas('categories', ['name' => 'Self-Help']);
    }

    /** @test */
    public function admin_can_create_book_with_cover_upload()
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->create('cover.jpg', 100, 'image/jpeg');

        $response = $this->actingAs($this->admin)->post('/admin/books', [
            'title' => 'The Great Book',
            'category_id' => $this->category->id,
            'author' => 'Author Name',
            'isbn' => '9781234567890',
            'publisher' => 'Publisher Inc',
            'publication_year' => 2023,
            'price' => 19.99,
            'stock' => 15,
            'description' => 'Test book description.',
            'cover_image' => $file,
            'status' => 'active',
        ]);

        $response->assertRedirect(route('admin.books.index'));
        $this->assertDatabaseHas('books', [
            'title' => 'The Great Book',
            'price' => 19.99,
            'stock' => 15,
        ]);
    }

    /** @test */
    public function admin_can_search_and_filter_books()
    {
        Book::create([
            'title' => 'Unique Searchable Book Title',
            'category_id' => $this->category->id,
            'author' => 'Unique Author',
            'price' => 25.00,
            'stock' => 5,
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/books?search=Unique Searchable');
        $response->assertStatus(200);
        $response->assertSee('Unique Searchable Book Title');
    }

    /** @test */
    public function admin_cannot_delete_category_with_assigned_books()
    {
        Book::create([
            'title' => 'Book assigned to category',
            'category_id' => $this->category->id,
            'price' => 10.00,
            'stock' => 5,
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->admin)->delete("/admin/categories/{$this->category->id}");
        $response->assertRedirect();
        $response->assertSessionHas('error', 'This category cannot be deleted because it is currently assigned to books.');
        $this->assertDatabaseHas('categories', ['id' => $this->category->id]);
    }
}
