<?php

namespace App\Jobs;

use App\Mail\ResponseEmailSend;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
// prod
// use Illuminate\Support\Facades\Mail;
// dev
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SendResponseEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $mailData;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($mailData)
    {
        $this->mailData = $mailData;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $mail = new ResponseEmailSend($this->mailData);
        // dev
        try {
            Storage::disk('local')->put('last_mail.html', $mail->build()->render());
            Log::info('Response mail sent');
        } catch (\ReflectionException $e) {
            Log::error($e->getMessage());
        }
        // prod
        // Mail::to($this->mailData['emailTo'])->send($mail);
    }
}
