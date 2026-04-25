<?php

namespace App\Jobs;

use App\Mail\NewDocumentNotification;
use App\Models\Document;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class NotifyWorkerOfNewDocument implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Document $document)
    {}

    public function handle(): void
    {
        $worker = $this->document->user;
        $targetEmail = 'miguel05.dev@gmail.com';
        
        Mail::to($targetEmail)->send(new NewDocumentNotification($this->document));
    }
}
