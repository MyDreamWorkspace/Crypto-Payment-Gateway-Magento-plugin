<?php

namespace Eligmaltd\GoCryptoPay\Lib;

class GoCryptoCommon
{
    /**
	 * It generates a random number of length 10.
	 * 
	 * @param length The length of the random string.
	 * 
	 * @return A string of random numbers.
	 */
	public function randomNumbers($length = 10) {
        $characters = '0123456789';
        $charactersLength = strlen($characters);
        $randomNumbers = '';
        for ($i = 0; $i < $length; $i++) {
            $randomNumbers .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomNumbers;
    }

    /**
	 * It generates a random string of characters.
	 * 
	 * @param length The length of the string you want to generate.
	 * 
	 * @return A random string of characters.
	 */
	public function randomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    /**
	 * It generates a random string of 48 characters, and then hashes it with SHA256
	 * 
	 * @return A hash of a random string of 48 characters.
	 */
	public function generatePaymentToken()
    {
        return hash('sha256', $this->randomString(48));
    }

    /**
	 * It takes an array of payment methods and returns a string of the payment method names separated
	 * by commas
	 * 
	 * @param array paymentMethods An array of payment methods.
	 * 
	 * @return The payment methods as a string.
	 */
	public function getPaymentMethodsAsString(array $paymentMethods) {
        $text = '';
        if (!empty($paymentMethods)) {
            foreach ($paymentMethods as $paymentMethod) {
                $text .= $paymentMethod['name'] . ', ';
            }
        }
        return rtrim($text, ', ');
    }
}
