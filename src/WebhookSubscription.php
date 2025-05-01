<?php

namespace xpanse\Sdk;

use Exception;

require_once(__DIR__ . '/tools/HttpWrapper.php');
require_once(__DIR__ . '/tools/ArrayTools.php');
require_once(__DIR__ . '/tools/UrlTools.php');
require_once(__DIR__ . '/tools/CaseConverter.php');

/**
 * @copyright xpanse
 */
class WebhookSubscription
{
    private array $validSearchKeys = [
        'AddedAfter', 'AddedBefore', 'Type', 'Id', 'SortBy', 'Limit', 'Skip',
    ];

    /**
     * @throws ResponseException
     */
    public function Create($params)
    {
        $params = CaseConverter::convertKeysToPascalCase($params);
        ArrayTools::ValidateKeys($params, ['Types', 'Url']);

        $data = $this->BuildCreateWebhookSubscriptionJson($params);

        $data = ArrayTools::CleanEmpty($data);

        return HttpWrapper::CallApi('/webhook/subscription', 'POST', json_encode($data));
    }

    /**
     * @throws ResponseException
     */
    public function Single($webhookSubscriptionId)
    {
        $url = '/webhook/subscription/' . urlencode($webhookSubscriptionId);

        return HttpWrapper::CallApi($url, 'GET', '');
    }

    /**
     * @throws ResponseException
     */
    public function Delete($webhookSubscriptionId)
    {
        $url = '/webhook/subscription/' . urlencode($webhookSubscriptionId);

        return HttpWrapper::CallApi($url, 'DELETE', '');
    }

    /**
     * @throws ResponseException
     */
    public function Search($params)
    {
        $params = CaseConverter::convertKeysToPascalCase($params);
        try {
            $url = '/webhook/subscription' . UrlTools::CreateQueryString($params, $this->validSearchKeys);
        } catch (Exception $ex) {
            throw new ResponseException($ex->getMessage(), 0, 0, false);
        }

        return HttpWrapper::CallApi($url, 'GET', '');
    }

    private function BuildCreateWebhookSubscriptionJson($params): array
    {
        $sourceParams =
            [
                'Url' => 1,
                'Types' => 1,
                'Authorization' => 1,
            ];
        return array_intersect_key($params, $sourceParams);
    }
}
