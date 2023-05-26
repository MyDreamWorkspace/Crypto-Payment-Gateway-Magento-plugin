<?php
namespace Eligmaltd\GoCryptoPay\Test\Unit\Gateway\Validator;

use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;
use Eligmaltd\GoCryptoPay\Gateway\Http\Client\ClientMock;
use Eligmaltd\GoCryptoPay\Gateway\Validator\ResponseCodeValidator;

class ResponseCodeValidatorTest extends \PHPUnit_Framework_TestCase
{
    /* @var Magento\Payment\Gateway\Validator\ResultInterfaceFactory $resultFactory*/
    private $resultFactory;
    /* @var Magento\Payment\Gateway\Validator\ResultInterface $resultMock*/
    private $resultMock;

    public function setUp()
    {
        $this->resultFactory = $this->getMockBuilder(
            'Magento\Payment\Gateway\Validator\ResultInterfaceFactory'
        )
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultMock = $this->getMock(ResultInterface::class);
    }

    public function testValidate(array $response, array $expectationToResultCreation)
    {
        $this->resultFactory->expects(static::once())
            ->method('create')
            ->with(
                $expectationToResultCreation
            )
            ->willReturn($this->resultMock);

        $validator = new ResponseCodeValidator($this->resultFactory);

        static::assertInstanceOf(
            ResultInterface::class,
            $validator->validate(['response' => $response])
        );
    }

    public function validateDataProvider()
    {
        return [
            'fail_1' => [
                'response' => [],
                'expectationToResultCreation' => [
                    'isValid' => false,
                    'failsDescription' => [__('Gateway rejected the transaction.')]
                ]
            ],
            'fail_2' => [
                'response' => [ResponseCodeValidator::RESULT_CODE => ClientMock::FAILURE],
                'expectationToResultCreation' => [
                    'isValid' => false,
                    'failsDescription' => [__('Gateway rejected the transaction.')]
                ]
            ],
            'success' => [
                'response' => [ResponseCodeValidator::RESULT_CODE => ClientMock::SUCCESS],
                'expectationToResultCreation' => [
                    'isValid' => true,
                    'failsDescription' => []
                ]
            ]
        ];
    }
}
