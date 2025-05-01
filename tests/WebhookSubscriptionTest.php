<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once(__DIR__ . '/TestConfiguration.php');
require_once(__DIR__ . '/../src/Config.php');
require_once(__DIR__ . '/../src/WebhookSubscription.php');
require_once(__DIR__ . '/TestBase.php');
require_once(__DIR__ . '/../src/ResponseException.php');

use xpanse\Sdk\WebhookSubscription;
use xpanse\Sdk\ResponseException;

final class WebhookSubscriptionTest extends TestBase
{
    private $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new \xpanse\Sdk\WebhookSubscription();
    }

    private function createWebhookSubscription()
    {
        return $this->service->Create([
                                          'Types' => ['Transaction'],
                                          'Url' => 'https://example',
                                      ]);
    }

    /**
     * @throws ResponseException
     * @throws Exception
     */
    public function testCreateWebhookSubscription(): void
    {
        $result = $this->createWebhookSubscription();

        $this->assertNotNull($result);
        $this->assertNotNull($result['webhookSubscriptionId']);
    }

    /**
     * @throws ResponseException
     * @throws Exception
     */
    public function testGetWebhookSubscription(): void
    {
        $webhook = $this->createWebhookSubscription();
        $result = $this->service->Single($webhook['webhookSubscriptionId']);

        $this->assertNotNull($result);
        $this->assertSame($webhook['webhookSubscriptionId'], $result['webhookSubscriptionId']);
    }

    /**
     * @throws ResponseException
     * @throws Exception
     */
    public function testSearchWebhookSubscription(): void
    {
        $webhook = $this->createWebhookSubscription();
        $result = $this->service->Search(['id' => $webhook['webhookSubscriptionId']]);

        $this->assertNotNull($result);
        $this->assertSame(1, $result['count']);
        $this->assertSame($webhook['webhookSubscriptionId'], $result['webhookSubscriptions'][0]['webhookSubscriptionId']);
    }


    /**
     * @throws ResponseException
     * @throws Exception
     */
    public function testDeleteWebhookSubscription(): void
    {
        $webhook = $this->createWebhookSubscription();
        $result = $this->service->Delete($webhook['webhookSubscriptionId']);

        $this->assertNotNull($result);
        $this->assertSame($webhook['webhookSubscriptionId'], $result['webhookSubscriptionId']);
    }
}
