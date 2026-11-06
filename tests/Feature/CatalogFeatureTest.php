<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Subcategory;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CatalogFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->actingAs(User::factory()->admin()->create());
    }

    public function test_category_crud_preserves_slugs_and_validates_names(): void
    {
        $this->post(route('admin.categories.store'), ['name' => ''])->assertSessionHasErrors('name');
        $this->post(route('admin.categories.store'), [
            'name' => 'Audio & Video',
            'description' => 'Equipment for work and play.',
        ])->assertRedirect(route('admin.categories.index'));

        $category = Category::where('slug', 'audio-video')->firstOrFail();

        $this->get(route('admin.categories.index'))->assertOk()->assertSee('Audio &amp; Video', false);
        $this->get(route('admin.categories.edit', $category))->assertOk();
        $this->post(route('admin.categories.store'), ['name' => $category->name])->assertSessionHasErrors('name');
        $this->put(route('admin.categories.update', $category), ['name' => 'Sound Equipment'])->assertRedirect();
        $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => 'Sound Equipment', 'slug' => 'audio-video']);

        $this->delete(route('admin.categories.destroy', $category))->assertRedirect();
        $this->assertModelMissing($category);
    }

    public function test_category_with_subcategories_cannot_be_deleted(): void
    {
        $subcategory = Subcategory::factory()->create();

        $this->delete(route('admin.categories.destroy', $subcategory->category))->assertSessionHasErrors('delete');
        $this->assertModelExists($subcategory->category);
        $this->assertModelExists($subcategory);
    }

    public function test_subcategory_crud_requires_a_valid_parent_and_scopes_duplicate_names(): void
    {
        $category = Category::factory()->create();
        $other = Category::factory()->create();

        $this->post(route('admin.subcategories.store'), ['name' => 'Audio', 'category_id' => 99999])
            ->assertSessionHasErrors('category_id');
        $this->post(route('admin.subcategories.store'), ['name' => 'Audio', 'category_id' => $category->id])->assertRedirect();
        $subcategory = Subcategory::where('category_id', $category->id)->firstOrFail();

        $this->post(route('admin.subcategories.store'), ['name' => 'Audio', 'category_id' => $category->id])
            ->assertSessionHasErrors('name');
        $this->post(route('admin.subcategories.store'), ['name' => 'Audio', 'category_id' => $other->id])->assertRedirect();
        $this->get(route('admin.subcategories.index', ['category' => $category->id]))
            ->assertOk()->assertSee($category->name);
        $this->get(route('admin.subcategories.edit', $subcategory))->assertOk();
        $this->put(route('admin.subcategories.update', $subcategory), [
            'name' => 'Headphones',
            'category_id' => $category->id,
        ])->assertRedirect();
        $this->assertDatabaseHas('subcategories', ['id' => $subcategory->id, 'name' => 'Headphones']);
        $this->delete(route('admin.subcategories.destroy', $subcategory))->assertRedirect();
        $this->assertModelMissing($subcategory);
    }

    public function test_populated_subcategory_cannot_be_deleted_or_moved(): void
    {
        $product = Product::factory()->create();
        $other = Category::factory()->create();

        $this->put(route('admin.subcategories.update', $product->subcategory), [
            'name' => $product->subcategory->name,
            'category_id' => $other->id,
        ])->assertSessionHasErrors('category_id');

        $this->delete(route('admin.subcategories.destroy', $product->subcategory))->assertSessionHasErrors('delete');
        $this->assertModelExists($product->subcategory);
        $this->assertModelExists($product);
    }

    public function test_valid_product_is_listed_uploaded_and_readable_by_slug(): void
    {
        Storage::fake('public');
        $subcategory = Subcategory::factory()->create();

        $this->post(route('admin.products.store'), $this->fields($subcategory) + [
            'image' => UploadedFile::fake()->image('headphones.png'),
        ])->assertRedirect(route('admin.products.index'));

        $product = Product::where('slug', 'studio-headphones')->firstOrFail();
        Storage::disk('public')->assertExists($product->image);
        $this->assertSame('89.99', $product->new_price);
        $this->get(route('admin.products.show', $product))->assertOk()->assertSee($product->name);
        $this->get(route('shop.products.show', $product->slug))->assertOk()->assertSee($product->name);
        $this->get(route('shop.products.show', 'does-not-exist'))->assertNotFound();
    }

    public function test_product_rejects_invalid_prices_and_mismatched_hierarchy(): void
    {
        Storage::fake('public');
        $subcategory = Subcategory::factory()->create();
        $other = Category::factory()->create();

        $this->post(route('admin.products.store'), array_replace($this->fields($subcategory), [
            'category_id' => $other->id,
            'old_price' => '2.00',
            'image' => UploadedFile::fake()->image('headphones.jpg'),
        ]))->assertSessionHasErrors(['subcategory_id', 'old_price']);

        foreach (['-1', '8.999', '1e4', '100000000.00'] as $price) {
            $this->post(route('admin.products.store'), array_replace($this->fields($subcategory), [
                'new_price' => $price,
                'image' => UploadedFile::fake()->image('headphones.jpg'),
            ]))->assertSessionHasErrors('new_price');
        }

        $this->assertDatabaseCount('products', 0);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_product_requires_a_real_allowed_image_on_create(): void
    {
        Storage::fake('public');
        $subcategory = Subcategory::factory()->create();
        $fields = $this->fields($subcategory);

        $this->post(route('admin.products.store'), $fields)->assertSessionHasErrors('image');
        $this->post(route('admin.products.store'), $fields + [
            'image' => UploadedFile::fake()->createWithContent('fake.png', '<?php echo 1;'),
        ])->assertSessionHasErrors('image');
        $this->post(route('admin.products.store'), $fields + [
            'image' => UploadedFile::fake()->image('oversize.png')->size(3200),
        ])->assertSessionHasErrors('image');

        $this->assertDatabaseCount('products', 0);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_duplicate_product_names_get_distinct_slugs_and_updates_keep_existing_links(): void
    {
        Storage::fake('public');
        $subcategory = Subcategory::factory()->create();

        for ($i = 0; $i < 2; $i++) {
            $this->post(route('admin.products.store'), $this->fields($subcategory) + [
                'image' => UploadedFile::fake()->image('headphones.jpg'),
            ])->assertRedirect(route('admin.products.index'));
        }

        $this->assertSame(['studio-headphones', 'studio-headphones-2'], Product::orderBy('id')->pluck('slug')->all());
        $product = Product::firstOrFail();
        $original = $product->image;

        $this->put(route('admin.products.update', $product), array_replace($this->fields($subcategory), [
            'name' => 'Studio Headphones Pro',
        ]))->assertRedirect();
        $this->assertSame('studio-headphones', $product->fresh()->slug);
        $this->assertSame($original, $product->fresh()->image);
        Storage::disk('public')->assertExists($original);
    }

    public function test_replacing_and_deleting_a_product_cleans_up_owned_images(): void
    {
        Storage::fake('public');
        $subcategory = Subcategory::factory()->create();
        $fields = $this->fields($subcategory);
        $this->post(route('admin.products.store'), $fields + [
            'image' => UploadedFile::fake()->image('original.png'),
        ])->assertRedirect(route('admin.products.index'));
        $product = Product::firstOrFail();
        $original = $product->image;

        $this->put(route('admin.products.update', $product), $fields + [
            'image' => UploadedFile::fake()->image('replacement.jpg'),
        ])->assertRedirect(route('admin.products.index'));

        $replacement = $product->fresh()->image;
        $this->assertNotSame($original, $replacement);
        Storage::disk('public')->assertMissing($original);
        Storage::disk('public')->assertExists($replacement);

        $this->delete(route('admin.products.destroy', $product))->assertRedirect(route('admin.products.index'));
        $this->assertModelMissing($product);
        Storage::disk('public')->assertMissing($replacement);
    }

    public function test_database_rejects_mismatched_product_hierarchy_even_outside_forms(): void
    {
        $subcategory = Subcategory::factory()->create();
        $other = Category::factory()->create();

        $this->expectException(QueryException::class);
        Product::factory()->for($subcategory)->for($other)->create();
    }

    public function test_storefront_filters_only_active_products_and_handles_malformed_search(): void
    {
        $product = Product::factory()->create(['name' => 'Voyager Mouse']);
        Product::factory()->create(['name' => 'Voyager Hidden', 'slug' => 'voyager-hidden', 'is_active' => false]);
        Product::factory()->create(['name' => 'Voyager Other']);

        $this->get(route('shop.index', ['q' => 'Voyager', 'category' => $product->category_id]))
            ->assertOk()->assertSee('Voyager Mouse')->assertDontSee('Voyager Other')->assertDontSee('Voyager Hidden');
        $this->get('/products?q[]=unexpected&category[]=1')->assertOk();
        $this->get(route('shop.products.show', 'voyager-hidden'))->assertNotFound();
    }

    private function fields(Subcategory $subcategory): array
    {
        return [
            'category_id' => $subcategory->category_id,
            'subcategory_id' => $subcategory->id,
            'name' => 'Studio Headphones',
            'description' => 'Comfortable headphones for everyday listening.',
            'old_price' => '109.99',
            'new_price' => '89.99',
            'is_active' => '1',
        ];
    }
}
