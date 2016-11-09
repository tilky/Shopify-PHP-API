<?php
/**
 * User: Anatoly Skornyakov
 * Email: anatoly@skornyakov.net
 * Date: 27/10/2016
 * Time: 13:10
 */

namespace ShopifyApi\Core;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class Proxy
{
    const BASE_URL = 'https://%s/admin/';

    private $_apiKey;

    private $_apiSecret;

    private $_storeUrl;

    public function __construct(string $apiKey, string $apiSecret, string $storeUrl, array $configs = [])
    {
        $this->_apiKey = $apiKey;
        $this->_apiSecret = $apiSecret;
        $this->_storeUrl = $storeUrl;

        $configs['http_client']['base_uri'] = sprintf(
            self::BASE_URL,
            $this->_storeUrl
        );

        $this->_httpClient = new Client($configs['http_client']);
    }

    public function getCustomers(array $params = [])
    {
        return $this->_getApi('customers.json', $params);
    }

    public function getCustomer(int $id)
    {
        return $this->_getApi(sprintf('customers/%d.json', $id));
    }

    public function findCustomers(array $params = [])
    {
        return $this->_getApi('customers/search.json', $params);
    }

    public function updateCustomer($id, $data)
    {
        return $this->_putApi(sprintf('customers/%d.json', $id), ['customer' => $data]);
    }

    public function createCustomer($data)
    {
        return $this->_postApi('customers.json', ['customer' => $data]);
    }

    public function customerSetDefaultAddress(int $customerId, int $addressId)
    {
        return $this->_putApi(sprintf(
            'customers/%d/addresses/%d/default.json',
            $customerId,
            $addressId
        ));
    }

    public function createCustomerAddress(int $customerId, $data)
    {
        return $this->_postApi(
            sprintf('customers/%d/addresses.json', $customerId),
            ['address' => $data]
        );
    }

    public function updateCustomerAddress(int $customerId, int $addressId, $data)
    {
        return $this->_putApi(
            sprintf('customers/%d/addresses/%d.json', $customerId, $addressId),
            ['address' => $data]
        );
    }

    private function _getApi(string $url, array $query = [])
    {
        return $this->_callApi('get', $url, ['query' => $query]);
    }

    private function _putApi(string $url, array $formParams = [])
    {
        return $this->_callApi('put', $url, ['form_params' => $formParams]);
    }

    private function _postApi(string $url, array $formParams = [])
    {
        return $this->_callApi('post', $url, ['form_params' => $formParams]);
    }

    private function _callApi(string $method, string $url, array $options = [])
    {
        $options['auth'] = [$this->_apiKey, $this->_apiSecret];
//        try {
            $response = $this->_httpClient->request($method, $url, $options);
//        var_dump($response->getBody()->getContents());
//        } catch (ClientException $exception) {
//            $data = json_decode($exception->getResponse()->getBody()->getContents());
//            switch (intval($data->response->code)) {
//                case 404: throw new NotFoundException($data->response->errors->error[0], 404, $exception);
//                case 505: throw new InternalServerErrorException($data->response->errors->error[0], 500, $exception);
//            }
//            throw $exception;
//        }
        $data = \json_decode($response->getBody()->getContents());
        return $data;
    }
}