<?php

namespace BinaryCats\MailgunWebhooks;

use Illuminate\Support\Arr;

/**
 * @property string $signature Resolves from $signatureArray
 * @property string|int $timestamp Resolves from $signatureArray
 * @property string $token Resolves from $signatureArray
 */
final class WebhookSignature
{
    /**
     * @var string[]
     */
    protected array $signatureArray;

    /**
     * Signature secret.
     *
     * @var string
     */
    protected string $secret;

    /**
     * @param  string[]  $signatureArray
     * @param  string  $secret
     */
    public function __construct(array $signatureArray, string $secret)
    {
        $this->signatureArray = $signatureArray;
        $this->secret = $secret;
    }

    /**
     * Static accessor into the class constructor.
     *
     * @param  string[]  $signatureArray
     * @param  string  $secret
     * @return WebhookSignature static
     */
    public static function make($signatureArray, string $secret)
    {
        return new static(Arr::wrap($signatureArray), $secret);
    }

    /**
     * True if the signature is valid.
     *
     * @return bool
     */
    public function verify(): bool
    {
        return hash_equals($this->signature, $this->computeSignature());
    }

    /**
     * Compute expected signature.
     *
     * @return string
     */
    protected function computeSignature()
    {
        $comparator = implode('', [
            $this->timestamp,
            $this->token,
        ]);

        return hash_hmac('sha256', $comparator, $this->secret);
    }

    /**
     * Magically access items from signature array.
     *
     * @param  string  $attribute
     * @return mixed
     */
    public function __get($attribute)
    {
        return Arr::get($this->signatureArray, $attribute);
    }
}
