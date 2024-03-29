<?php

use App\Enums\DocumentType;
use App\Enums\Language;
use App\Events\MetaDescriptionGenerated;
use App\Jobs\Blog\CreateMetaDescription;
use App\Jobs\RegisterAppUsage;
use App\Models\Document;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;

beforeEach(function () {
    $this->document = Document::factory()->create([
        'type' => DocumentType::BLOG_POST->value,
        'language' => Language::ENGLISH->value,
        'meta' => []
    ]);
});

describe(
    'Blog - CreateMetaDescription job',
    function () {

        it('can be serialized', function () {
            $job = new CreateMetaDescription($this->document, [
                'process_id' => Str::uuid()
            ]);
            $serialized = serialize($job);
            expect($serialized)->toBeString();
        });

        it('generates meta description and triggers completed event', function () {
            Event::fake([MetaDescriptionGenerated::class]);
            Bus::fake(RegisterAppUsage::class);

            $processId = Str::uuid();
            $job = new CreateMetaDescription($this->document, [
                'process_id' => $processId
            ]);
            $job->handle();

            $this->assertDatabaseHas('document_content_blocks', [
                'document_id' => $this->document->id,
                'type' => 'meta_description',
                'content' => 'AI content generated',
                'prompt' => '',
                'order' => 1
            ]);

            Bus::assertDispatched(RegisterAppUsage::class);

            Event::assertDispatched(MetaDescriptionGenerated::class, function ($event) use ($processId) {
                return $event->processId == $processId && $event->document->id === $this->document->id;
            });
        });
    }
)->group('blog');
