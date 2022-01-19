<?php

return [

    /*
     * Mailgun will sign each webhook using a secret. You can find the used secret at the
     * webhook configuration settings: https://app.mailgun.com/app/account/security/api_keys.
     */
    'signing_secret' => env('MAILGUN_WEBHOOK_SECRET'),

    /*
     * You can define the job that should be run when a certain webhook hits your application
     * here. The key is the name of the Mailgun event type with the `.` replaced by a `_`.
     *
     * You can find a list of Mailgun webhook types here:
     * https://documentation.mailgun.com/en/latest/user_manual.html#events.
     *
     * The package will automatically convert the keys to lowercase, but you should
     * be congnisant of the fact that array keys are case sensitive
     */
    'jobs' => [
        // 'delivered' => \BinaryCats\MailgunWebhooks\Jobs\HandleDelivered::class,
    ],

    /*
     * The classname of the model to be used. The class should equal or extend
     * Spatie\WebhookClient\Models\WebhookCall
     */
    'model' => \Spatie\WebhookClient\Models\WebhookCall::class,

    /*
     * The classname of the model to be used. The class should equal or extend
     * BinaryCats\MailgunWebhooks\ProcessMailgunWebhookJob
     */
    'process_webhook_job' => \BinaryCats\MailgunWebhooks\ProcessMailgunWebhookJob::class,
];
