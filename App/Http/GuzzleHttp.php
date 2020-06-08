<?php
declare (strict_types=1);

namespace LanhaiVideo\App\Http;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;

class GuzzleHttp
{

    public $guzzle;

    public function __construct($proxy)
    {
        $guzzle = new Client([
            'timeout'   =>  5.0
        ]);
        if ($proxy) {
            $guzzle->setProxyHttp($proxy['ip'],$proxy['port']);
        }
        $this->guzzle = $guzzle;
    }

    //基础URL
    private $baseUri;

    //超时时间
    private $timeout;

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

    public function getBaseOptions()
    {
        $options = [
            'base_uri' => property_exists($this, 'baseUri') ? $this->baseUri : '',
            'timeout' => property_exists($this, 'timeout') ? $this->timeout : 5.0,
        ];
        return $options;
    }

    /**
     * 创建一个客户端
     * @param array $options
     * @return Client
     */
    public function getHttpClient(array $options = [])
    {
        $guzzle = new Client($options);
        return new Client($options);
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
