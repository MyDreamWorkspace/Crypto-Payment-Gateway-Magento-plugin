<?php
namespace Eligmaltd\GoCryptoPay\Model;

class ConfigFactory
{
    /* @var \Magento\Framework\ObjectManagerInterface $_objectManager*/
    protected $_objectManager = null;
    /* @var string $_instanceName*/
    protected $_instanceName = null;

    /**
     * A constructor function.
     * 
     * @param \Magento\Framework\ObjectManagerInterface objectManager The object manager is a class
     * that is used to create new objects and call methods on existing objects.
     * @param instanceName The name of the class you want to instantiate.
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        $instanceName = "\\Eligmaltd\\GoCryptoPay\\Model\\Config"
    ) {
        $this->_objectManager = $objectManager;
        $this->_instanceName = $instanceName;
    }

    /**
     * It creates an instance of the class specified in the protected variable `` and
     * passes it the data array
     * 
     * @param array data The data to be passed to the model.
     * 
     * @return An instance of the class that is being called.
     */
    public function create(array $data = [])
    {
        return $this->_objectManager->create($this->_instanceName, $data);
    }
}
