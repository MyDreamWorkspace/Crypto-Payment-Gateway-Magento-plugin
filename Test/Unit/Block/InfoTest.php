<?php
namespace Eligmaltd\GoCryptoPay\Test\Unit\Block;

use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Model\InfoInterface;
use Eligmaltd\GoCryptoPay\Block\Info;

class InfoTest extends \PHPUnit_Framework_TestCase
{
    /* @var Magento\Framework\View\Element\Template\Context $context*/
    protected $context;
    /* @var Magento\Payment\Gateway\ConfigInterface $config*/
    protected $config;
    /* @var Magento\Payment\Model\InfoInterface $paymentInfoModel*/
    protected $paymentInfoModel;

    /**
     * It creates a mock object for the Context class.
     */
    public function setUp()
    {
        $this->context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->config = $this->getMock(ConfigInterface::class);
        $this->paymentInfoModel = $this->getMock(InfoInterface::class);
    }

    /**
     * > This function tests the getSpecificationInformation function of the Info class
     */
    public function testGetSpecificationInformation()
    {
        $this->config->expects(static::once())
            ->method('getValue')
            ->willReturnMap(
                [
                    ['paymentInfoKeys', null, $this->getPaymentInfoKeys()]
                ]
            );
        $this->paymentInfoModel->expects(static::atLeastOnce())
            ->method('getAdditionalInformation')
            ->willReturnMap(
                $this->getAdditionalFields()
            );

        $info = new Info(
            $this->context,
            $this->config,
            [
                'is_secure_mode' => 0,
                'info' => $this->paymentInfoModel
            ]
        );

        static::assertSame($this->getExpectedResult(), $info->getSpecificInformation());
    }

    /**
     * > This function tests the getSpecificationInformationSecure function
     */
    public function testGetSpecificationInformationSecure()
    {
        $this->config->expects(static::exactly(2))
            ->method('getValue')
            ->willReturnMap(
                [
                    ['paymentInfoKeys', null, $this->getPaymentInfoKeys()],
                    ['privateInfoKeys', null, $this->getPrivateInfoKeys()]
                ]
            );
        $this->paymentInfoModel->expects(static::atLeastOnce())
            ->method('getAdditionalInformation')
            ->willReturnMap(
                $this->getAdditionalFields()
            );

        $info = new Info(
            $this->context,
            $this->config,
            [
                'is_secure_mode' => 1,
                'info' => $this->paymentInfoModel
            ]
        );

        static::assertSame($this->getSecureExpectedResult(), $info->getSpecificInformation());
    }

    /**
     * It returns an array of arrays, each of which has two elements: the first is the name of the
     * field, the second is the value of the field
     * 
     * @return An array of arrays.
     */
    private function getAdditionalFields()
    {
        return [
            ['FRAUD_MSG_LIST', ['Some issue happened', 'And some other happened too']],
            ['non_info_field', 'X'],
            ['PUBLIC_DATA', 'Payed with USD']
        ];
    }

    /**
     * It returns a string of comma separated values
     * 
     * @return a string of comma separated values.
     */
    private function getPaymentInfoKeys()
    {
        return 'FRAUD_MSG_LIST,PUBLIC_DATA';
    }

    /**
     * > It returns a string
     * 
     * @return The private method getPrivateInfoKeys() is being returned.
     */
    private function getPrivateInfoKeys()
    {
        return 'FRAUD_MSG_LIST';
    }

    /**
     * > It returns an array of strings, where the first string is a translation of the string
     * 'FRAUD_MSG_LIST' and the second string is a translation of the string 'PUBLIC_DATA'
     * 
     * @return The result of the method getExpectedResult()
     */
    private function getExpectedResult()
    {
        return [
            (string)__('FRAUD_MSG_LIST') => 'Some issue happened; And some other happened too',
            (string)__('PUBLIC_DATA') => 'Payed with USD'
        ];
    }

    /**
     * It returns an array of data that is expected to be returned by the function.
     * 
     * @return The function getSecureExpectedResult() is being returned.
     */
    private function getSecureExpectedResult()
    {
        return [
            (string)__('PUBLIC_DATA') => 'Payed with USD'
        ];
    }
}
