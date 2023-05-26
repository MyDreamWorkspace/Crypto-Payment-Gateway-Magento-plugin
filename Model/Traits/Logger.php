<?php

namespace Eligmaltd\GoCryptoPay\Model\Traits;

trait Logger
{
    /* @var arrary $_debugData*/
    protected $_debugData = [];

    /**
     * It writes debug data to the log file if the debug flag is set to true
     */
    protected function _writeDebugData()
    {
        if ($this->getConfigHelper()->getDebug()) {
        $this->getLogger()->debug($this->_getDebugMessage());
        }
    }

    /**
     * > This function adds a key/value pair to the `_debugData` array
     * 
     * @param key The name of the parameter.
     * @param value The value to be set.
     * 
     * @return The object itself.
     */
    protected function _addDebugData($key, $value)
    {
        $this->_debugData[$key] = $value;
        return $this;
    }

    /**
     * > This function returns a string representation of the debug data
     * 
     * @return The return value is the string representation of the debugData array.
     */
    protected function _getDebugMessage() 
    {
        return var_export($this->_debugData, true);
    }
}
