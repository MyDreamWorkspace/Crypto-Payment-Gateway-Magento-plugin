<?php
namespace Eligmaltd\GoCryptoPay\Gateway\Http;

use Magento\Payment\Gateway\Http\TransferBuilder;
use Magento\Payment\Gateway\Http\TransferFactoryInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Eligmaltd\GoCryptoPay\Gateway\Request\MockDataRequest;

class TransferFactory implements TransferFactoryInterface
{
    /* @var Magento\Payment\Gateway\Http\TransferBuilder $transferBuilder*/
    private $transferBuilder;

    /**
     * > This function is called when the class is instantiated. It takes a `TransferBuilder` object as
     * an argument and assigns it to the `` property
     * 
     * @param TransferBuilder transferBuilder This is the object that will be used to build the
     * transfer object.
     */
    public function __construct(
        TransferBuilder $transferBuilder
    ) {
        $this->transferBuilder = $transferBuilder;
    }

    /**
     * > This function creates a new transfer object with the body of the request, the method of the
     * request, and the headers of the request
     * 
     * @param array request The request data that will be sent to the API.
     * 
     * @return A TransferInterface object
     */
    public function create(array $request)
    {
        return $this->transferBuilder
            ->setBody($request)
            ->setMethod('POST')
            ->setHeaders(
                [
                    'force_result' => isset($request[MockDataRequest::FORCE_RESULT])
                        ? $request[MockDataRequest::FORCE_RESULT]
                        : null
                ]
            )
            ->build();
    }
}
