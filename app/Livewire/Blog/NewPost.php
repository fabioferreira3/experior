<?php

namespace App\Livewire\Blog;

use App\Enums\Language;
use App\Enums\SourceProvider;
use App\Enums\Style;
use App\Enums\Tone;
use App\Exceptions\CreatingBlogPostException;
use App\Exceptions\InsufficientUnitsException;
use App\Jobs\Blog\PrepareCreationTasks;
use App\Repositories\DocumentRepository;
use App\Rules\CsvFile;
use App\Rules\DocxFile;
use App\Rules\PdfFile;
use App\Traits\UnitCheck;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use WireUi\Traits\Actions;
use Livewire\Component;
use Livewire\WithFileUploads;

class NewPost extends Component
{
    use Actions, WithFileUploads, UnitCheck;

    public mixed $context;
    public array $sourceUrls;
    public string $source;
    public $fileInput = null;
    public $filePath = null;
    public string $tempSourceUrl;
    public bool $maxSourceUrlsReached;
    public string $language;
    public array $languages;
    public string $keyword;
    public string $tone;
    public string $style;
    public string $targetHeadersCount;
    public $imgPrompt;
    public bool $modal;
    public bool $generateImage;
    public string $title;

    public function __construct()
    {
        $this->title = __('blog.new_blog_post');
        $this->source = SourceProvider::FREE_TEXT->value;
        $this->context = null;
        $this->sourceUrls = [];
        $this->tempSourceUrl = '';
        $this->language = 'en';
        $this->languages = Language::getLabels();
        $this->keyword = '';
        $this->generateImage = false;
        $this->imgPrompt = null;
        $this->targetHeadersCount = '3';
        $this->tone = 'default';
        $this->style = 'default';
    }

    public function render()
    {
        return view('livewire.blog.new')->title($this->title);
    }

    public function rules()
    {
        return [
            'context' => ['required', 'string', 'max:30000'],
            'sourceUrls' => [
                'required_if:source,youtube,website_url',
                'array',
                function ($attribute, $value, $fail) {
                    if ($this->source === SourceProvider::YOUTUBE->value && count($value) > 3) {
                        return $fail('The maximum number of Youtube sources is 3.');
                    }

                    if ($this->source === SourceProvider::WEBSITE_URL->value && count($value) > 5) {
                        return $fail('The maximum number of source URLs is 5.');
                    }
                },
            ],
            'sourceUrls.*' => ['url', $this->source === 'youtube' ? new \App\Rules\YouTubeUrl() : ''],
            'source' => [
                'required',
                Rule::in(array_map(fn ($value) => $value->value, SourceProvider::cases()))
            ],
            'keyword' => 'required',
            'language' => 'required|in:en,pt,es,fr,de,it,ru,ja,ko,ch,pl,el,ar,tr',
            'targetHeadersCount' => 'required|numeric|min:2|max:10',
            'tone' => ['nullable', Rule::in(Tone::getValues())],
            'style' => ['nullable', Rule::in(Style::getValues())],
            'fileInput' => [
                'required_if:source,docx,pdf,csv',
                'max:51200', // in kilobytes, 50mb = 50 * 1024 = 51200kb
                new DocxFile($this->source),
                new PdfFile($this->source),
                new CsvFile($this->source),
            ],
            'imgPrompt' => ['required_if:generateImage,true']
        ];
    }

    public function messages()
    {
        return
            [
                'context.required' => __('validation.blog_post_context_required'),
                'sourceUrls.required_if' => __('validation.blog_post_sourceurl_required'),
                'sourceUrls.*.url' => __('validation.active_url'),
                'keyword.required' => __('validation.keyword_required'),
                'language.required' => __('validation.language_required'),
                'targetHeadersCount.min' => __('validation.min_subtopics', ['min' => 2]),
                'targetHeadersCount.max' => __('validation.max_subtopics', ['max' => 10]),
                'targetHeadersCount.required' => __('validation.subtopics_count'),
                'imgPrompt.required_if' => __('validation.img_prompt_required'),
            ];
    }

    public function updatedGenerateImage($value)
    {
        if (!$value) {
            $this->imgPrompt = null;
        }
    }

    public function storeFile()
    {
        $accountId = Auth::check() ? Auth::user()->account_id : 'guest';
        $filename = Str::uuid() . '.' . $this->fileInput->getClientOriginalExtension();
        $this->filePath = "documents/$accountId/" . $filename;
        $this->fileInput->storeAs("documents/$accountId", $filename, 's3');
    }

    public function validateSourceUrls()
    {
        if (in_array($this->source, [
            SourceProvider::YOUTUBE->value,
            SourceProvider::WEBSITE_URL->value
        ]) && $this->tempSourceUrl !== '' && !count($this->sourceUrls)) {
            $this->sourceUrls[] = $this->tempSourceUrl;
            $this->tempSourceUrl = '';
        }
    }

    public function process()
    {
        $this->validateSourceUrls();
        $this->validate();

        try {
            $this->validateUnitCosts();

            if ($this->fileInput) {
                $this->storeFile();
            }

            $params = [
                'source' => $this->source,
                'context' => $this->context,
                'language' => $this->language,
                'meta' => [
                    'source_urls' => $this->sourceUrls ?? [],
                    'source_file_path' => $this->filePath ?? null,
                    'target_headers_count' => $this->targetHeadersCount,
                    'tone' => $this->tone,
                    'style' => $this->style,
                    'keyword' => $this->keyword,
                    'img_prompt' => $this->imgPrompt ?? null,
                    'generate_image' => $this->generateImage
                ]
            ];
            $repo = new DocumentRepository();
            $document = $repo->createBlogPost($params);
            PrepareCreationTasks::dispatch($document, $params);

            return redirect()->route('blog-post-processing-view', ['document' => $document]);
        } catch (InsufficientUnitsException $e) {
            $this->dispatch(
                'alert',
                type: 'error',
                message: __('alerts.insufficient_units')
            );
        } catch (Exception $e) {
            throw new CreatingBlogPostException($e->getMessage());
        }
    }

    public function validateUnitCosts()
    {
        $this->totalCost = 0;
        $this->estimateWordsGenerationCost($this->targetHeadersCount * 350);

        if ($this->imgPrompt ?? false) {
            $this->estimateImageGenerationCost(1);
        }
        $this->authorizeTotalCost();
    }

    public function checkMaxSourceUrls()
    {
        $isMaxReached = false;

        if (($this->source === SourceProvider::YOUTUBE->value && count($this->sourceUrls) >= 3) ||
            ($this->source === SourceProvider::WEBSITE_URL->value && count($this->sourceUrls) >= 5)
        ) {
            $isMaxReached = true;
        }

        $this->maxSourceUrlsReached = $isMaxReached;
    }

    public function addSourceUrl()
    {
        if ($this->source === SourceProvider::YOUTUBE->value) {
            $validator = Validator::make(
                ['url' => $this->tempSourceUrl],
                [
                    'url' => [
                        'required',
                        'url',
                        function ($attribute, $value, $fail) {
                            // Check if it's a valid YouTube URL
                            if (!preg_match('/^(https?:\/\/)?(www\.)?(youtube\.com|youtu\.be)\/.+$/', $value)) {
                                return $fail('The ' . $attribute . ' must be a valid YouTube URL.');
                            }
                        },
                    ],
                ]
            );
            $validationMsg = 'This is not a valid Youtube URL.';
        } else {
            $validator = Validator::make(['url' => $this->tempSourceUrl], [
                'url' => 'required|url',
            ]);
            $validationMsg = 'The URL is not valid.';
        }

        if ($validator->fails()) {
            $this->addError('tempSourceUrl', $validationMsg);
            return;
        }

        if (!in_array($this->tempSourceUrl, $this->sourceUrls, true)) {
            $this->sourceUrls[] = $this->tempSourceUrl;
        }

        $this->tempSourceUrl = '';
        $this->checkMaxSourceUrls();
    }

    public function removeSourceUrl(string $sourceUrl)
    {
        $this->sourceUrls = array_filter($this->sourceUrls, function ($url) use ($sourceUrl) {
            return $url !== $sourceUrl;
        });

        $this->sourceUrls = array_values($this->sourceUrls);
        $this->checkMaxSourceUrls();
    }
}
