<?php

namespace App\Repositories;

use App\Enums\DocumentTaskEnum;
use App\Packages\ChatGPT\ChatGPT;
use App\Enums\LanguageModels;
use App\Helpers\PromptHelperFactory;
use App\Jobs\DispatchDocumentTasks;
use App\Models\Document;
use App\Models\DocumentContentBlock;
use App\Models\MediaFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Talendor\StabilityAI\Enums\StabilityAIEngine;
use Talendor\StabilityAI\StabilityAIClient;

class GenRepository
{
    public static function generateTitle(Document $document, $context)
    {
        $repo = new DocumentRepository($document);
        $promptHelper = PromptHelperFactory::create($document->language->value);
        $chatGpt = new ChatGPT(LanguageModels::GPT_3_TURBO->value);
        $response = $chatGpt->request([[
            'role' => 'user',
            'content' => $promptHelper->writeTitle($context, [
                'tone' => $document->getMeta('tone'),
                'keyword' => $document->getMeta('keyword')
            ])
        ]]);
        $document->update(['title' => Str::of(str_replace(["\r", "\n"], '', $response['content']))->trim()->trim('"')]);
        $repo->addHistory(
            [
                'field' => 'title',
                'content' => $response['content']
            ],
            $response['token_usage']
        );
    }

    public static function generateMetaDescription(Document $document)
    {
        $repo = new DocumentRepository($document);
        $promptHelper = PromptHelperFactory::create($document->language->value);
        $chatGpt = new ChatGPT(LanguageModels::GPT_3_TURBO->value);
        $response = $chatGpt->request([[
            'role' => 'user',
            'content' => $promptHelper->writeMetaDescription(
                $document->normalized_structure,
                [
                    'tone' => $document->getMeta('tone'),
                    'keyword' => $document->getMeta('keyword')
                ]
            )
        ]]);
        $repo->updateMeta('meta_description', Str::of(str_replace(["\r", "\n"], '', $response['content']))->trim()->trim('"'));
        $repo->addHistory(
            [
                'field' => 'meta_description',
                'content' => $response['content']
            ],
            $response['token_usage']
        );
    }

    public static function generateImage(Document $document, array $params)
    {
        $client = app(StabilityAIClient::class);
        $results = $client->textToImage($params);
        if (count($results)) {
            foreach ($results as $result) {
                self::processImageResult($document, $result, $params);
            }

            if ($params['add_content_block'] ?? false) {
                $document->contentBlocks()->save(new DocumentContentBlock([
                    'type' => 'media_file_image',
                    'content' => 'ai-images/' . $results[0]['fileName'],
                    'prompt' => $params['prompt'],
                    'order' => 1
                ]));
            }
        }
    }

    public static function generateImageVariants(Document $document, array $params)
    {
        $client = app(StabilityAIClient::class);
        $params['init_image'] = Storage::disk('s3')->get($params['file_name']);
        $results = $client->imageToImage($params);
        if (count($results)) {
            foreach ($results as $result) {
                self::processImageResult($document, $result, $params);
            }
        }
    }

    public static function generateSocialMediaPost(Document $document, string $platform)
    {
        $repo = new DocumentRepository($document);
        $promptHelper = PromptHelperFactory::create($document->language->value);
        $chatGpt = new ChatGPT();
        $response = $chatGpt->request([
            [
                'role' => 'user',
                'content' =>   $promptHelper->writeSocialMediaPost($document->getContext(), [
                    'keyword' => $document->getMeta('keyword'),
                    'platform' => $platform,
                    'tone' => $document->getMeta('tone'),
                    'style' => $document->getMeta('style'),
                    'more_instructions' => $document->getMeta('more_instructions')
                ])
            ]
        ]);

        $document->contentBlocks()->save(
            new DocumentContentBlock([
                'type' => 'text',
                'content' => $response['content']
            ])
        );

        $repo->addHistory(
            [
                'field' => $platform,
                'content' => $response['content']
            ],
            $response['token_usage']
        );
    }

    public static function rewriteTextBlock(DocumentContentBlock $contentBlock, array $params)
    {
        $model = isset($params['faster']) && $params['faster'] ? LanguageModels::GPT_3_TURBO : LanguageModels::GPT_4;
        $repo = new DocumentRepository($contentBlock->document);
        $promptHelper = PromptHelperFactory::create($contentBlock->document->language->value);
        $chatGpt = new ChatGPT($model->value);
        $response = $chatGpt->request([[
            'role' => 'user',
            'content' => $promptHelper->generic($params['prompt'], $params['text'])
        ]]);
        $contentBlock->update(['content' => $response['content']]);
        $repo->addHistory(
            [
                'field' => 'title',
                'content' => $response['content']
            ],
            $response['token_usage']
        );
    }

    public static function paraphraseDocument(Document $document)
    {
        $document->refresh();
        $repo = new DocumentRepository($document);
        $processId = Str::uuid();

        foreach ($document->meta['original_sentences'] as $sentence) {
            $repo->createTask(DocumentTaskEnum::PARAPHRASE_TEXT, [
                'order' => 1,
                'process_id' => $processId,
                'meta' => [
                    'text' => $sentence['text'],
                    'sentence_order' => $sentence['sentence_order']
                ]
            ]);
        }

        $repo->createTask(DocumentTaskEnum::REGISTER_FINISHED_PROCESS, [
            'order' => 99,
            'process_id' => $processId,
            'meta' => [
                'silently' => true
            ]
        ]);

        DispatchDocumentTasks::dispatch($document);

        return $processId;
    }

    public static function paraphraseText(Document $document, array $params)
    {
        $processId = $params['process_id'] ?? Str::uuid();
        $repo = new DocumentRepository($document);
        $order = $params['order'] ?? 1;
        $repo->createTask(DocumentTaskEnum::PARAPHRASE_TEXT, [
            'order' => $params['order'] ?? 1,
            'process_id' => $processId,
            'meta' => [
                'text' => $params['text'],
                'sentence_order' => $params['sentence_order'],
                'tone' => $params['tone'] ?? null
            ]
        ]);
        $repo->createTask(DocumentTaskEnum::REGISTER_FINISHED_PROCESS, [
            'order' => $order + 1,
            'process_id' => $processId,
            'meta' => [
                'silently' => true
            ]
        ]);

        DispatchDocumentTasks::dispatch($document);

        return $processId;
    }

    public static function textToSpeech($document, array $params = [])
    {
        DocumentRepository::createTask(
            $document->id,
            DocumentTaskEnum::TEXT_TO_SPEECH,
            [
                'meta' => [
                    'input_text' => $params['input_text'],
                    'voice_id' => $params['voice_id'],
                ],
                'process_id' => $params['process_id']
            ]
        );

        DispatchDocumentTasks::dispatch($document);
    }

    private static function processImageResult($document, $result, $params): MediaFile
    {
        $repo = new DocumentRepository($document);
        $mediaFile = MediaRepository::storeImage($document->account, [
            'fileName' => $result['fileName'],
            'imageData' => $result['imageData'],
            'meta' => [
                'document_id' => $document->id,
                'process_id' => $params['process_id'] ?? null,
                'style_preset' => $params['style_preset'] ?? null,
                'model' => StabilityAIEngine::SD_XL_V_1->value,
                'steps' => $params['steps'] ?? 0,
                'prompt' => $params['prompt'] ?? null
            ]
        ]);
        $repo->addHistory(
            [
                'field' => 'image_generation',
                'content' => $mediaFile->id,
                'word_count' => 0,
                'char_count' => 0
            ],
            [
                'model' => StabilityAIEngine::SD_XL_V_1->value,
            ]
        );

        return $mediaFile;
    }
}
