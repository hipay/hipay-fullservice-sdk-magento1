<?php

/**
 * An interface description for Zend_Http_Client_Adapter_Stream classes.
 *
 * This interface decribes Zend_Http_Client_Adapter which supports streaming.

 */
interface Allopass_Hipay_Model_Api_Http_Client_Adapter_Stream
{
    /**
     * Set output stream
     *
     * This function sets output stream where the result will be stored.
     *
     * @param resource $stream Stream to write the output to
     *
     */
    public function setOutputStream($stream);
}
