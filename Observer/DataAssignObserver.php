<?php
namespace Eligmaltd\GoCryptoPay\Observer;

use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Psr\Log\LoggerInterface as Logger;

class DataAssignObserver extends AbstractDataAssignObserver
{
    /* @var Psr\Log\LoggerInterface $logger*/
    protected $logger;

    /**
     * > This function takes a Logger object as an argument and assigns it to the  property
     * 
     * @param Logger logger This is the logger object that we will use to log messages.
     */
    public function __construct(
        Logger $logger
    ) {
        $this->logger = $logger;
    }

    /**
     * It reads the data from the observer and sets the transaction_result as an additional information
     * to the payment info
     * 
     * @param Observer observer The observer object that is passed to the event.
     */
    public function execute(Observer $observer)
    {
        $method = $this->readMethodArgument($observer);
        $data = $this->readDataArgument($observer);

        $paymentInfo = $method->getInfoInstance();

        if ($data->getDataByKey('transaction_result') !== null) {
            $paymentInfo->setAdditionalInformation(
                'transaction_result',
                $data->getDataByKey('transaction_result')
            );
        }
    }
}
