<?php

namespace Tests\Feature;

use App\Models\File;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FileManagementTest extends TestCase
{
    use RefreshDatabase;

    private function makeFile(User $user, string $originalName = 'doc.txt'): File
    {
        Storage::fake('local');

        $content = 'contenido de prueba';
        $path = 'uploads/'.$user->id.'/x.txt';

        Storage::disk('local')->put($path, $content);

        return File::create([
            'user_id' => $user->id,
            'name' => 'x.txt',
            'original_name' => $originalName,
            'path' => $path,
            'mime_type' => 'text/plain',
            'size' => strlen($content),
            'extension' => 'txt',
        ]);
    }

    public function test_index_returns_200_for_authenticated_user(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('files.index'));

        $response->assertStatus(200);
    }

    public function test_index_does_not_show_files_from_other_users(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $file = $this->makeFile($user2, 'secret.txt');

        $response = $this->actingAs($user1)->get(route('files.index'));

        $response->assertStatus(200);
        $response->assertDontSee('secret.txt');
    }

    public function test_store_accepts_valid_file_and_redirects(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();

        $uploaded = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        $response = $this->actingAs($user)->post(route('files.store'), [
            'file' => $uploaded,
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseCount('files', 1);

        $record = File::first();
        $this->assertSame('document.pdf', $record->original_name);
        Storage::disk('local')->assertExists($record->path);
    }

    public function test_store_rejects_request_without_file_field(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('files.store'), []);

        $response->assertSessionHasErrors('file');
        $this->assertDatabaseCount('files', 0);
    }

    public function test_show_returns_200_for_owner(): void
    {
        $user = User::factory()->create();
        $file = $this->makeFile($user);

        $response = $this->actingAs($user)->get(route('files.show', $file));

        $response->assertStatus(200);
    }

    public function test_show_returns_403_for_different_user(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $file = $this->makeFile($owner);

        $response = $this->actingAs($intruder)->get(route('files.show', $file));

        $response->assertStatus(403);
    }

    public function test_download_returns_200_for_owner_and_checks_content_disposition_header(): void
    {
        $user = User::factory()->create();
        $file = $this->makeFile($user);

        $response = $this->actingAs($user)->get(route('files.download', $file));

        $response->assertStatus(200);
        $response->assertHeader('Content-Disposition');
    }

    public function test_download_returns_403_for_different_user(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $file = $this->makeFile($owner);

        $response = $this->actingAs($intruder)->get(route('files.download', $file));

        $response->assertStatus(403);
    }

    public function test_destroy_removes_record_and_file_from_storage(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $file = $this->makeFile($user);

        $response = $this->actingAs($user)->delete(route('files.destroy', $file));

        $response->assertRedirect();
        $this->assertDatabaseCount('files', 0);
        Storage::disk('local')->assertMissing($file->path);
    }

    public function test_destroy_returns_403_for_different_user(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $file = $this->makeFile($owner);

        $response = $this->actingAs($intruder)->delete(route('files.destroy', $file));

        $response->assertStatus(403);
        $this->assertDatabaseCount('files', 1);
    }

    public function test_unauthenticated_routes_redirect_to_login(): void
    {
        $this->get(route('files.index'))->assertRedirect(route('login'));
        $this->get(route('files.create'))->assertRedirect(route('login'));
        $this->post(route('files.store'), [])->assertRedirect(route('login'));
    }

    public function test_download_returns_404_if_stored_file_is_missing(): void
    {
        $user = User::factory()->create();
        $file = $this->makeFile($user);
        Storage::disk('local')->delete($file->path);

        $this->actingAs($user)->get(route('files.download', $file))->assertNotFound();
    }

    public function test_uploaded_files_are_private_and_use_detected_mime_type(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        $user = User::factory()->create();
        $path = tempnam(sys_get_temp_dir(), 'file-manager-test-');
        file_put_contents($path, 'Texto privado');
        $upload = new UploadedFile($path, 'document.html', 'text/html', null, true);

        $this->actingAs($user)->post(route('files.store'), ['file' => $upload])
            ->assertRedirect(route('files.index'));

        $file = File::firstOrFail();
        $this->assertSame('text/plain', $file->mime_type);
        Storage::disk('local')->assertExists($file->path);
        Storage::disk('public')->assertMissing($file->path);
    }

    public function test_store_rejects_files_larger_than_50_mb(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('files.store'), [
            'file' => UploadedFile::fake()->create('big.zip', 51201),
        ])->assertSessionHasErrors('file');

        $this->assertDatabaseCount('files', 0);
        Storage::disk('local')->assertDirectoryEmpty('uploads');
    }

    public function test_search_returns_matching_original_filename(): void
    {
        $user = User::factory()->create();
        $this->makeFile($user, 'factura-noviembre.txt');

        $this->actingAs($user)->get(route('files.index', ['search' => 'factura']))
            ->assertOk()->assertSee('factura-noviembre.txt');
        $this->actingAs($user)->get(route('files.index', ['search' => 'ausente']))
            ->assertOk()->assertDontSee('factura-noviembre.txt');
    }

    public function test_download_forces_attachment_and_disables_content_sniffing(): void
    {
        $user = User::factory()->create();
        $file = $this->makeFile($user, 'document.html');

        $response = $this->actingAs($user)->get(route('files.download', $file));

        $response->assertOk()
            ->assertDownload('document.html')
            ->assertHeader('Content-Type', 'application/octet-stream')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeaderContains('Cache-Control', 'no-store');
    }

    public function test_download_returns_404_when_stored_file_is_missing(): void
    {
        $user = User::factory()->create();
        $file = $this->makeFile($user);
        Storage::disk('local')->delete($file->path);

        $response = $this->actingAs($user)->get(route('files.download', $file));

        $response->assertNotFound();
    }

    public function test_upload_detects_content_instead_of_trusting_client_mime_type(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $source = UploadedFile::fake()->createWithContent('report.php', 'A plain text report');
        $uploaded = new UploadedFile($source->getPathname(), 'report.php', 'image/png', null, true);

        $response = $this->actingAs($user)->post(route('files.store'), ['file' => $uploaded]);

        $response->assertRedirect(route('files.index'))->assertSessionHasNoErrors();
        $record = File::firstOrFail();
        $this->assertSame('text/plain', $record->mime_type);
        $this->assertSame('txt', $record->extension);
        Storage::disk('local')->assertExists($record->path);
    }

    public function test_upload_rejects_files_over_50_megabytes(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('files.store'), [
            'file' => UploadedFile::fake()->create('large.zip', 51201),
        ]);

        $response->assertSessionHasErrors('file');
        $this->assertDatabaseCount('files', 0);
        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_index_filters_by_original_name_and_displays_readable_metadata(): void
    {
        $user = User::factory()->create();
        $file = $this->makeFile($user, 'budget.txt');

        $response = $this->actingAs($user)->get(route('files.index', ['search' => 'budget']));

        $response->assertOk()->assertSee('budget.txt')->assertSee('text/plain');
        $this->assertSame(1, $response->viewData('files')->total());
    }

    public function test_index_excludes_files_that_do_not_match_the_type_filter(): void
    {
        $user = User::factory()->create();
        $this->makeFile($user, 'notes.txt');

        $response = $this->actingAs($user)->get(route('files.index', ['type' => 'image/']));

        $response->assertOk()->assertDontSee('notes.txt');
        $this->assertSame(0, $response->viewData('files')->total());
    }

    public function test_unauthenticated_download_redirects_to_login(): void
    {
        $file = $this->makeFile(User::factory()->create());

        $response = $this->get(route('files.download', $file));

        $response->assertRedirect(route('login'));
    }
}
