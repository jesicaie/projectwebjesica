<?php

namespace Tests\Feature;

use App\Models\Book;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_books_can_be_created_listed_viewed_updated_and_deleted(): void
    {
        $bookData = [
            'title' => 'Laskar Pelangi',
            'author' => 'Andrea Hirata',
            'publisher' => 'Bentang Pustaka',
            'published_year' => 2005,
            'description' => 'Novel tentang kehidupan di Belitung.',
        ];

        $this->postJson('/api/books', $bookData)
            ->assertCreated()
            ->assertJsonPath('title', 'Laskar Pelangi')
            ->assertJsonPath('published_year', 2005);

        $book = Book::query()->firstOrFail();

        $this->getJson('/api/books')
            ->assertOk()
            ->assertJsonPath('data.0.id', $book->id);

        $this->getJson("/api/books/{$book->id}")
            ->assertOk()
            ->assertJsonPath('author', 'Andrea Hirata');

        $this->patchJson("/api/books/{$book->id}", ['title' => 'Laskar Pelangi Baru'])
            ->assertOk()
            ->assertJsonPath('title', 'Laskar Pelangi Baru');

        $this->deleteJson("/api/books/{$book->id}")
            ->assertOk()
            ->assertJsonPath('message', 'Buku berhasil dihapus.');

        $this->assertDatabaseMissing('books', ['id' => $book->id]);
    }

    public function test_book_creation_requires_title_and_author(): void
    {
        $this->postJson('/api/books', ['publisher' => 'Bentang Pustaka'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['title', 'author']);
    }
}
