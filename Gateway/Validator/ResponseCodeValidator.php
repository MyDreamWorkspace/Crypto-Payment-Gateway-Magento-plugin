<?php
namespace GoCryptoPay\GoCryptoPay\Gateway\Validator;

use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Eligmaltd\GoCryptoPay\Gateway\Http\Client\ClientMock;

class ResponseCodeValidator extends AbstractValidator
{
    public const RESULT_CODE = 'RESULT_CODE';

    /**
     * If the transaction is successful, return true. If the transaction is not successful, return
     * false
     * 
     * @param array validationSubject This is an array of data that is passed to the validate function.
     * 
     * @return The result of the validation.
     */
    public function validate(array $validationSubject)
    {
        if (!isset($validationSubject['response']) || !is_array($validationSubject['response'])) {
            throw new \InvalidArgumentException('Response does not exist');
        }

        $response = $validationSubject['response'];

        if ($this->isSuccessfulTransaction($response)) {
            return $this->createResult(
                true,
                []
            );
        } else {
            return $this->createResult(
                false,
                [__('Gateway rejected the transaction.')]
            );
        }
    }

    /**
     * > This function checks if the response from the client is successful
     * 
     * @param array response The response from the API.
     * 
     * @return The result of the transaction.
     */
    private function isSuccessfulTransaction(array $response)
    {
        return isset($response[self::RESULT_CODE])
        && $response[self::RESULT_CODE] !== ClientMock::FAILURE;
    }
}
