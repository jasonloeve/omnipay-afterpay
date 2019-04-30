<?php

namespace Omnipay\AfterPay\Message;

use Omnipay\Common\Exception\InvalidRequestException;

class PurchaseRequest extends AbstractRequest
{
    /**
     * Get the raw data array for this message. The format of this varies from gateway to
     * gateway, but will usually be either an associative array, or a SimpleXMLElement.
     *
     * @return mixed
     * @throws \Omnipay\Common\Exception\InvalidRequestException
     */
    public function getData()
    {
        /** @var \Omnipay\Common\CreditCard $card */
        $card = $this->getCard();

        // Normalize consumer names as AfterPay will reject the request with a missing surname
        $givenNames = 'Test';
        $surname    = 'Name';

        if (empty($surname) && false !== $pos = strrpos($givenNames, ' ')) {
            $surname = substr($givenNames, $pos + 1);
            $givenNames = substr($givenNames, 0, $pos);
        }

        // Append fix query param to urls with existing query params as AfterPay appends their
        // data in a way that can break the base url
        $returnUrl = $this->getReturnUrl();
        $cancelUrl = $this->getCancelUrl();

        if (strpos($returnUrl, '?') !== false) {
            $returnUrl .= '&_fix=';
        }

        if (strpos($cancelUrl, '?') !== false) {
            $cancelUrl .= '&_fix=';
        }

        $data = array(
            'totalAmount'     => array(
                'amount'      => '40.70',
                'currency'    => 'NZD',
            ),
            'consumer'        => array(
                'phoneNumber' => '0400000000',
                'givenNames'  => 'Test',
                'surname'     => 'Name',
                'email'       => 'test@gmail.com',
            ),
            'billing'         => array(
                'name'        => 'Test',
                'line1'       => '57 Test Rd',
                'line2'       => '',
                'suburb'      => 'Auckland',
                'state'       => 'Auckland',
                'postcode'    => '1010',
                'countryCode' => 'NZ',
                'phoneNumber' => '0400000000',
            ),
            'shipping'        => array(
                'name'        => 'Test',
                'line1'       => '57 Test Rd',
                'line2'       => '',
                'suburb'      => 'Auckland',
                'state'       => 'Auckland',
                'postcode'    => '1010',
                'countryCode' => 'NZ',
                'phoneNumber' => '0400000000',
            ),
            'items'           => $this->getItemData(),
            'merchant'        => array(
                // Need to append dummy parameter otherwise AfterPay breaks the hash param on return
                'redirectConfirmUrl' => 'https://www.merchant.com/confirm',
                'redirectCancelUrl'  => 'https://www.merchant.com/cancel',
            ),
            'merchantReference' => 'merchantOrder-1234',
            //'merchantReference' => $this->getTransactionReference(),
        );

        return $data;
    }

    /**
     * @return array
     * @throws \Omnipay\Common\Exception\InvalidRequestException
     */
    public function getShippingAmount()
    {
        $items = $this->getItems();
        $itemArray = array();

        if ($items !== null) {
            /** @var \Omnipay\Common\ItemInterface $item */
            foreach ($items as $item) {
                $itemArray[] = array(
                    'name'     => 'Test Product',
                    'quantity' => '1',
                    'price'    => array(
                        'amount'   => '40',
                        'currency' => 'NZD',
                    ),
                );
            }
        }

        return $itemArray;
    }

    /**
     * @return array
     * @throws \Omnipay\Common\Exception\InvalidRequestException
     */
    public function getItemData()
    {
        $items = $this->getItems();
        $itemArray = array();

        if ($items !== null) {
            /** @var \Omnipay\Common\ItemInterface $item */
            foreach ($items as $item) {
                $itemArray[] = array(
                    'name'     => 'Test Product',
                    'quantity' => '1',
                    'price'    => array(
                        'amount'   => '40',
                        'currency' => 'NZD',
                    ),
                );
            }
        }

        return $itemArray;
    }

    /**
     * @return string
     */
    public function getEndpoint()
    {
        return parent::getEndpoint() . '/orders';
    }

    /**
     * @param mixed $data
     * @return \Omnipay\AfterPay\Message\Response
     */
    protected function createResponse($data)
    {
        return new PurchaseResponse($this, $data);
    }

    /**
     * @param string|float|int $amount
     * @return null|string
     * @throws \Omnipay\Common\Exception\InvalidRequestException
     */
    protected function formatPrice($amount)
    {
        if ($amount) {
            if (!is_float($amount) &&
                $this->getCurrencyDecimalPlaces() > 0 &&
                false === strpos((string) $amount, '.')
            ) {
                throw new InvalidRequestException(
                    'Please specify amount as a string or float, ' .
                    'with decimal places (e.g. \'10.00\' to represent $10.00).'
                );
            }

            return $this->formatCurrency($amount);
        }

        return null;
    }
}
