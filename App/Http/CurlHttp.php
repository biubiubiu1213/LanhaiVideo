<?php
declare (strict_types=1);

namespace LanhaiVideo\App\Http;

use Curl\Curl;
use function LanhaiVideo\App\dump;

class CurlHttp
{
    /**
     * @var Curl
     */
    public $curl;

    /**
     * CurlHttp constructor.
     * @param $proxy
     * @throws \ErrorException
     */
    public function __construct($proxy)
    {
        $curl = new Curl();
        if ($proxy) {
            $curl->setProxy($proxy['ip'],$proxy['port']);
        }
        $this->curl = $curl;
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
        $res = $this->request('get', $url, [
            'headers' => $headers,
            'query' => $query,
        ]);
        if (is_object($res)) {
            $res = json_decode(json_encode($res),true);
        }
        return $res;
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
        $response = $this->curl;
        $response->setHeaders($headers);
        $response->get($url, $query);
        if ($response->getHttpStatusCode() == 302 || $response->getHttpStatusCode() == 301) {
            $headers = $response->getResponseHeaders();
            if (isset($headers['location'])) {
                return $headers['location'];
            }
            if (isset($headers['Location'])) {
                return $headers['Location'];
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
            $options['query'] = $data;
        } else {
            $options['query'] = $data;
        }

        $res = $this->request('post', $url, $options);
        if (is_object($res)) {
            $res = json_decode( json_encode( $res),true);
        }
        return $res;
    }

    /**
     * 公共请求方法
     * @param $method string 方法 | GET | POST
     * @param $url string url
     * @param array $params array 参数
     * @return mixed|string
     */
    public function request($method, $url, $params = [],&$curl = null)
    {
        $headers = $params['headers'] ?? [];
        $query = $params['query'] ?? [];
        $curl = $curl ?? $this->curl;
        $curl->setHeaders($headers);
        $curl->setReferer($url);
        $curl->{$method}($url, $query);
//        echo 'code:' . $curl->getHttpStatusCode(),"\r\n";
        if ($curl->getHttpStatusCode() == 302 || $curl->getHttpStatusCode() == 301) {
            $location = $curl->getResponseHeaders()['location'];
            $this->request($method,$location,$params,$curl);
        }
        return $this->unwrapResponse($curl);
    }

    /**
     * @param $curl
     * @return mixed|string
     */
    public function unwrapResponse($curl)
    {
        $contentType = $curl->getResponseHeaders()['Content-Type'];
        $contents = $curl->getResponse();
        if (!$contents || !$contentType) return '';
        if (false !== stripos($contentType, 'json') || stripos($contentType, 'javascript')) {
            if (is_string($contents)) {
                return json_decode($contents, true);
            } else {
                return $contents;
            }
        } elseif (false !== stripos($contentType, 'xml')) {
            return json_decode(json_encode(simplexml_load_string($contents, 'SimpleXMLElement', LIBXML_NOCDATA), JSON_UNESCAPED_UNICODE), true);
        }
        return $contents;
    }

}
