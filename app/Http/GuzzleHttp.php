<?php
declare (strict_types=1);

namespace LanhaiVideo\app\Http;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;

class GuzzleHttp
{
    /**
     * @var Client
     */
    public $guzzle;

    /**
     * GuzzleHttp constructor.
     * @param $proxy
     */
    public function __construct($proxy)
    {
        $setting = [
            'timeout'   =>  10.0
        ];
        if ($proxy) {
            $setting['proxy'] = [
                'http' => $proxy['ip'] . ':' . $proxy['port']
            ];
        }
        $guzzle = new Client($setting);
        $this->guzzle = $guzzle;
    }
    /**
     * 公共GET方法
     * @param $url
     * @param array $query
     * @param array $headers
     * @return mixed|string
     */
    public function get($url, $query = [], $headers = [])
    {
        return $this->request('get', $url, [
            'headers' => $headers,
            'query' => $query,
        ]);
    }

    /**
     * 公共GET方法
     * @param $url
     * @param array $query
     * @param array $headers
     * @return mixed|string
     */
    public function redirects($url, $query = [], $headers = [])
    {
        $response = $this->guzzle->get($url, [
            'headers' => $headers,
            'query' => $query,
            'allow_redirects' => false
        ]);
        if ($response->getStatusCode() == 302 || $response->getStatusCode() == 301) {
            $headers = $response->getHeaders();
            if (isset($headers['location'][0])) {
                return $headers['location'][0];
            }
            if (isset($headers['Location'][0])) {
                return $headers['Location'][0];
            }
            return "";
        }
        return "";
    }

    /**
     * 公共POST方法
     * @param $url
     * @param $data
     * @param array $headers
     * @return mixed|string
     */
    public function post($url, $data, $headers = [])
    {
        $options = [
            'headers' => $headers,
        ];

        if (!is_array($data)) {
            $options['body'] = $data;
        } else {
            $options['form_params'] = $data;
        }

        return $this->request('post', $url, $options);
    }

    /**
     * 公共请求方法
     * @param $method string 方法 | GET | POST
     * @param $url string url
     * @param array $params array 参数
     * @return mixed|string
     */
    public function request($method, $url, $params = [])
    {
        return $this->unwrapResponse($this->guzzle->{$method}($url, $params));
    }

    /**
     * @param ResponseInterface $response
     * @return mixed|string
     */
    public function unwrapResponse(ResponseInterface $response)
    {
        $contentType = $response->getHeaderLine('Content-Type');
        $contents = $response->getBody()->getContents();
        if (false !== stripos($contentType, 'json') || stripos($contentType, 'javascript')) {
            return json_decode($contents, true);
        } elseif (false !== stripos($contentType, 'xml')) {
            return json_decode(json_encode(simplexml_load_string($contents, 'SimpleXMLElement', LIBXML_NOCDATA), JSON_UNESCAPED_UNICODE), true);
        }
        return $contents;
    }

}
