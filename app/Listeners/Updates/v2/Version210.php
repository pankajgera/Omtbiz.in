<?php

namespace App\Listeners\Updates\v2;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\Events\UpdateFinished;
use App\Listeners\Updates\Listener;
use App\Models\Setting;
use App\Models\CompanySetting;

class Version210 extends Listener
{
    const VERSION = '2.1.0';

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(UpdateFinished $event)
    {
        if ($this->isListenerFired($event)) {
            return;
        }

        // Add initial auto generate value
        $this->addAutoGenerateSettings();

        // Update Crater app version
        Setting::setSetting('version', static::VERSION);
    }

    private function addAutoGenerateSettings()
    {
        $settings = [
            'invoice_auto_generate' => 'YES',
            'invoice_prefix' => 'INV',
            'estimate_prefix' => 'EST',
            'estimate_auto_generate' => 'YES',
            'payment_prefix' => 'PAY',
            'payment_auto_generate' => 'YES'
        ];

        foreach ($settings as $key => $value) {
            CompanySetting::setSetting(
                $key,
                $value,
                auth()->user()->company->id
            );
        }
    }
}
