<?php
namespace Eligmaltd\GoCryptoPay\Test\Unit\Model\Ui;

use Eligmaltd\GoCryptoPay\Gateway\Http\Client\ClientMock;
use Eligmaltd\GoCryptoPay\Model\Ui\ConfigProvider;

class ConfigProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testGetConfig()
    {
        $configProvider = new ConfigProvider();

        static::assertEquals(
            [
                'payment' => [
                    ConfigProvider::CODE => [
                        'transactionResults' => [
                            ClientMock::SUCCESS => __('Success'),
                            ClientMock::FAILURE => __('Fraud')
                        ]
                    ]
                ]
            ],
            $configProvider->getConfig()
        );
    }
}
