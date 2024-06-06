<?php

use App\Enums\DocumentType;
use App\Enums\Language;
use App\Jobs\ExpandTextSection;
use App\Models\Document;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->document = Document::factory()->create([
        'type' => DocumentType::BLOG_POST->value,
        'language' => Language::ENGLISH->value,
        'meta' => [
            'user_id' => $this->user->id
        ]
    ]);
});

describe(
    'Blog - ExpandTextSection job',
    function () {

        it('can be serialized', function () {
            $job = new ExpandTextSection($this->document, ['meta' => 'data']);
            $serialized = serialize($job);
            expect($serialized)->toBeString();
        });
    }
)->group('blog');
