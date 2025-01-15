<?php
class WebhookTools
{
    static function DeserializeTransaction($requestBody, $signatureHeader, $signatureKey)
    {
        if (!WebhookTools::IsFromXpanse($requestBody, $signatureHeader, $signatureKey)) {
            throw new InvalidArgumentException("Request body is not from xpanse");
        }

        $transaction = json_decode($requestBody, false, 512, JSON_BIGINT_AS_STRING);

        if ($transaction === null) {
            throw new InvalidArgumentException("Invalid JSON format");
        }

        return $transaction;
    }

    private static function IsFromXpanse($requestBody, $signatureHeader, $signatureKey): bool
    {
        $requestBytes = mb_convert_encoding($requestBody, 'UTF-8');
        $secret = mb_convert_encoding($signatureKey, 'UTF-8');

        try {
            $hmac = hash_hmac("sha256", $requestBytes, $secret, true);
            $hashString = base64_encode($hmac);

            return $hashString === $signatureHeader;
        } catch (Exception $e) {
            return false;
        }
    }
}
