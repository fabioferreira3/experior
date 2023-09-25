<?php

namespace App\Http\Livewire\SocialMediaPost\Platforms;

use App\Models\Document;
use App\Models\Traits\SocialMediaTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;

class FacebookPost extends Component
{
    use SocialMediaTrait;

    public Document $document;
    public bool $displayHistory = false;
    public string $userId;
    private string $platform;

    public function render()
    {
        return view('livewire.social-media-post.platforms.facebook-post');
    }

    public function showHistoryModal()
    {
        $this->displayHistory = true;
        $this->emit('listDocumentHistory', $this->platform, true);
    }

    public function closeHistoryModal()
    {
        $this->displayHistory = false;
    }
}
