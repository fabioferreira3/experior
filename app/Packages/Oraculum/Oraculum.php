<?php

namespace App\Packages\Oraculum;

use App\Enums\DataType;
use App\Helpers\SupportHelper;
use App\Models\User;
use App\Packages\Oraculum\Exceptions\AddSourceException;
use App\Packages\Oraculum\Exceptions\ChatRequestException;
use App\Packages\Oraculum\Exceptions\CreateBotException;
use App\Packages\Oraculum\Exceptions\DeleteCollectionException;
use App\Packages\Oraculum\Exceptions\QueryRequestException;
use Exception;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * @codeCoverageIgnore
 */
class Oraculum
{
    protected $client;
    protected $defaultBody;
    protected $user;
    protected $appId;

    public function __construct(User $user, string $appId)
    {
        $this->user = $user;
        $token = JWTAuth::fromUser($this->user);
        $this->appId = $appId;
        $this->defaultBody = [
            'app_id' => $appId,
            'token' => $token
        ];

        $this->client = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept-Encoding' => 'gzip, deflate, br'
        ])->baseUrl(config('oraculum.url'))
            ->timeout(90);
    }

    public function createBot()
    {
        try {
            if ($this->user->bot_enabled) {
                return;
            }

            $response = $this->client
                ->post('/new_user_bot', $this->defaultBody);

            if ($response->failed()) {
                $response->throw();
            }

            if ($response->successful()) {
                $this->user->update(['bot_enabled' => true]);
            }
        } catch (Exception $e) {
            throw new CreateBotException($e->getMessage());
        }
    }

    public function add(DataType $dataType, string $source)
    {
        try {
            $response = $this->client
                ->post('/add', array_merge($this->defaultBody, [
                    'data_type' => $dataType->value,
                    'url_or_text' => $source
                ]));

            if ($response->failed()) {
                $response->throw();
            }

            if ($response->successful()) {
                return $response->json('data');
            }
        } catch (Exception $e) {
            throw new AddSourceException($e->getMessage());
        }
    }

    public function query($message, $type = null)
    {
        if (SupportHelper::isTestModeEnabled()) {
            return $this->mockResponse($message);
        }

        try {
            if (!$this->user->bot_enabled) {
                $this->createBot();
            }

            $requestTokens = $this->countTokens($message);

            $response = $this->client
                ->post('/query', array_merge($this->defaultBody, [
                    'message' => $message,
                    'type' => $type ?? null
                ]));

            if ($response->failed()) {
                return $response->throw();
            }

            if ($response->successful()) {
                $responseData = $response->json('data');
                if (is_array($responseData) || is_object($responseData)) {
                }
                $responseTokens = $this->countTokens($responseData);
                return [
                    'content' => $responseData,
                    'token_usage' => [
                        'model' => !$type ? 'gpt-4-1106-preview' : 'gpt-3.5-turbo',
                        'prompt' => $requestTokens,
                        'completion' => $responseTokens,
                        'total' => $requestTokens + $responseTokens
                    ]
                ];
            }
        } catch (Exception $e) {
            throw new QueryRequestException($e->getMessage());
        }
    }

    public function chat($message)
    {
        if (SupportHelper::isTestModeEnabled()) {
            return $this->mockResponse($message);
        }

        try {
            if (!$this->user->bot_enabled) {
                $this->createBot();
            }

            $requestTokens = $this->countTokens($message);

            $response = $this->client
                ->post('/chat', array_merge($this->defaultBody, [
                    'message' => $message
                ]));

            if ($response->failed()) {
                return $response->throw();
            }

            if ($response->successful()) {
                $responseTokens = $this->countTokens($response->json('data'));
                return [
                    'content' => $response->json('data'),
                    'token_usage' => [
                        'model' => 'gpt-3.5-turbo',
                        'prompt' => $requestTokens,
                        'completion' => $responseTokens,
                        'total' => $requestTokens + $responseTokens
                    ]
                ];
            }
        } catch (Exception $e) {
            throw new ChatRequestException($e->getMessage());
        }
    }

    public function deleteCollection()
    {
        try {
            if (!$this->user->bot_enabled) {
                $this->createBot();
            }

            $response = $this->client
                ->post('/delete-collection', array_merge($this->defaultBody, []));

            if ($response->failed()) {
                return $response->throw();
            }

            if ($response->successful()) {
                return [
                    'data' => $response->json('data')
                ];
            }
        } catch (Exception $e) {
            throw new DeleteCollectionException($e->getMessage());
        }
    }

    public function countTokens($string)
    {
        Artisan::call('count:token', ['string' => addslashes($string)]);
        return (int) Artisan::output();
    }

    private function mockResponse(string $message)
    {
        $faker = Faker::create();
        $sleepCounter = $faker->numberBetween(2, 6);
        $wordsCount = $faker->numberBetween(10, 250);
        $response = $faker->words($wordsCount, true);
        $promptTokens = $this->countTokens($message);
        $completionTokens = $this->countTokens($response);

        sleep($sleepCounter);
        return [
            'content' => $response,
            'token_usage' => [
                'model' => 'gpt-4',
                'prompt' => $promptTokens,
                'completion' => $completionTokens,
                'total' => $promptTokens + $completionTokens
            ]
        ];
    }
}
