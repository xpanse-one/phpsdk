<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once(__DIR__ . '/TestConfiguration.php');
require_once(__DIR__ . '/../src/Config.php');
require_once(__DIR__ . '/../src/Subscription.php');
require_once(__DIR__ . '/../src/Customer.php');
require_once(__DIR__ . '/TestBase.php');
require_once(__DIR__ . '/../src/ResponseException.php');

use xpanse\Sdk\Subscription;
use xpanse\Sdk\Customer;
use xpanse\Sdk\ResponseException;

final class SubscriptionTest extends TestBase
{
    /**
     * @throws ResponseException
     * @throws Exception
     */
    public function testCreateSubscription(): void
    {
        $customerSvc = new Customer();

        $customerResult = $customerSvc->CreateWithCard([
                                                           'ProviderId' => TestConfiguration::getProviderId(),
                                                           'PaymentInformation' => [
                                                               'CardNumber' => '4111111111111111',
                                                               'ExpiryDate' => '10/30',
                                                               'Ccv' => '123',
                                                               'Cardholder' => 'Test Cardholder']]);

        $svc = new Subscription();

        $paymentMethodId = $customerResult['defaultPaymentMethod']['paymentMethodId'];
        $result = $svc->CreateSubscription($this->getNewSubscription($paymentMethodId));

        $this->assertSame($paymentMethodId, $result['paymentMethodId']);
        $this->assertSame('Active', $result['status']);
    }

    /**
     * @throws ResponseException
     * @throws Exception
     */
    public function testGetSubscription(): void
    {
        $customerSvc = new Customer();

        $customerResult = $customerSvc->CreateWithCard([
                                                           'ProviderId' => TestConfiguration::getProviderId(),
                                                           'PaymentInformation' => [
                                                               'CardNumber' => '4111111111111111',
                                                               'ExpiryDate' => '10/30',
                                                               'Ccv' => '123',
                                                               'Cardholder' => 'Test Cardholder']]);

        $svc = new Subscription();

        $paymentMethodId = $customerResult['defaultPaymentMethod']['paymentMethodId'];

        $newSubscription = $svc->CreateSubscription($this->getNewSubscription($paymentMethodId));
        $subscription = $svc->GetSubscription(['SubscriptionId' => $newSubscription['subscriptionId']]);

        $this->assertSame($paymentMethodId, $subscription['paymentMethodId']);
        $this->assertSame('Active', $subscription['status']);
    }


    /**
     * @throws ResponseException
     * @throws Exception
     */
    public function testDeleteSubscription(): void
    {
        $customerSvc = new Customer();

        $customerResult = $customerSvc->CreateWithCard([
                                                           'ProviderId' => TestConfiguration::getProviderId(),
                                                           'PaymentInformation' => [
                                                               'CardNumber' => '4111111111111111',
                                                               'ExpiryDate' => '10/30',
                                                               'Ccv' => '123',
                                                               'Cardholder' => 'Test Cardholder']]);

        $svc = new Subscription();

        $paymentMethodId = $customerResult['defaultPaymentMethod']['paymentMethodId'];

        $newSubscription = $svc->CreateSubscription($this->getNewSubscription($paymentMethodId));
        $subscription = $svc->DeleteSubscription(['SubscriptionId' => $newSubscription['subscriptionId']]);

        $this->assertSame($paymentMethodId, $subscription['paymentMethodId']);
        $this->assertSame('Cancelled', $subscription['status']);
    }

    /**
     * @throws ResponseException
     * @throws Exception
     */
    public function testPauseSubscription(): void
    {
        $customerSvc = new Customer();

        $customerResult = $customerSvc->CreateWithCard([
                                                           'ProviderId' => TestConfiguration::getProviderId(),
                                                           'PaymentInformation' => [
                                                               'CardNumber' => '4111111111111111',
                                                               'ExpiryDate' => '10/30',
                                                               'Ccv' => '123',
                                                               'Cardholder' => 'Test Cardholder']]);

        $svc = new Subscription();

        $paymentMethodId = $customerResult['defaultPaymentMethod']['paymentMethodId'];
        $subscription = $svc->CreateSubscription($this->getNewSubscription($paymentMethodId));

        $result = $svc->UpdateSubscriptionStatus($subscription['subscriptionId'], ['Status' => 'Suspended']);

        $this->assertSame('Suspended', $result['status']);
    }

    /**
     * @throws ResponseException
     * @throws Exception
     */
    public function testReactivateSubscription(): void
    {
        $customerSvc = new Customer();

        $customerResult = $customerSvc->CreateWithCard([
                                                           'ProviderId' => TestConfiguration::getProviderId(),
                                                           'PaymentInformation' => [
                                                               'CardNumber' => '4111111111111111',
                                                               'ExpiryDate' => '10/30',
                                                               'Ccv' => '123',
                                                               'Cardholder' => 'Test Cardholder']]);

        $svc = new Subscription();

        $paymentMethodId = $customerResult['defaultPaymentMethod']['paymentMethodId'];
        $subscription = $svc->CreateSubscription($this->getNewSubscription($paymentMethodId));

        $svc->UpdateSubscriptionStatus($subscription['subscriptionId'], ['Status' => 'Suspended']);
        $result = $svc->UpdateSubscriptionStatus($subscription['subscriptionId'], ['Status' => 'Active']);

        $this->assertSame('Active', $result['status']);
    }

    /**
     * @throws ResponseException
     * @throws Exception
     */
    public function testUpdateSubscription(): void
    {
        $customerSvc = new Customer();

        $customerResult = $customerSvc->CreateWithCard([
                                                           'ProviderId' => TestConfiguration::getProviderId(),
                                                           'PaymentInformation' => [
                                                               'CardNumber' => '4111111111111111',
                                                               'ExpiryDate' => '10/30',
                                                               'Ccv' => '123',
                                                               'Cardholder' => 'Test Cardholder']]);

        $svc = new Subscription();

        $paymentMethodId = $customerResult['defaultPaymentMethod']['paymentMethodId'];
        $result = $svc->CreateSubscription($this->getNewSubscription($paymentMethodId));

        $resultUpdate = $svc->UpdateSubscription($result['subscriptionId'], $this->getUpdateSubscription());

        $this->assertSame($result['subscriptionId'], $resultUpdate['subscriptionId']);
        $this->assertSame(200, $resultUpdate['amount']);
        $this->assertSame('AUD', $resultUpdate['currency']);
        $this->assertSame('Day', $resultUpdate['interval']);
        $this->assertSame(2, $resultUpdate['frequency']);
        $this->assertSame(3, $resultUpdate['endAfter']['count']);
        $this->assertSame(4, $resultUpdate['retry']['maximum']);
        $this->assertSame(5, $resultUpdate['retry']['frequency']);
        $this->assertSame('Hour', $resultUpdate['retry']['interval']);
        $this->assertSame('https://example.com/webhoo2', $resultUpdate['webhook']['url']);
        $this->assertSame('secret2', $resultUpdate['webhook']['authorization']);        
    }
     
    private function getNewSubscription($paymentMethodId): array
    {
        return [
            'EndAfter' => ['Count'=>2],
            'Retry' => ['Maximum' => 3,
                        'Frequency' => 1,
                        'Interval' => 'Day'
                        ],
            'Webhook' => [
                            'Url' => 'https://example.com/webhoo',
                            'Authorization' => 'secret'
                        ],
            'PaymentMethodId' => $paymentMethodId,
            'Amount' => 100,
            'Currency' => 'USD',
            'Interval' => 'Month',
            'Frequency' => 1
        ];
    }

    private function getUpdateSubscription(): array
    {
        return [
            'EndAfter' => ['Count'=>3],
            'Retry' => ['Maximum' => 4,
                        'Frequency' => 5,
                        'Interval' => 'Hour'
                        ],
            'Webhook' => [
                            'Url' => 'https://example.com/webhoo2',
                            'Authorization' => 'secret2'
                        ],
            'Amount' => 200,
            'Currency' => 'AUD',
            'Interval' => 'Day',
            'Frequency' => 2
        ];
    }
}
