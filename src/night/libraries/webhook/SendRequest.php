<?php

namespace night\libraries\webhook;

class SendRequest
{
    public function __construct(private Webhook $webhook, private Message $message) {}

    public function execute()
    {
        $ch = curl_init($this->webhook->getURL());
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($this->message));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        if (!$this->message->hasFile()) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        } else {
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: multipart/form-data']);
        }
        $response = [curl_exec($ch), curl_getinfo($ch, CURLINFO_RESPONSE_CODE)];
        curl_close($ch);
        return $response;
    }
}
