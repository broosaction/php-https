<?php
/**
 * Created by Bruce Mubangwa on 07 /Nov, 2020 @ 2:02
 */

namespace Https\Target;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class Response implements ResponseInterface
{
    public $code;
    public $raw_body;
    public $body;
    public $headers;

    /**
     * @param int $code response code of the cURL request
     * @param string $raw_body the raw body of the cURL response
     * @param string $headers raw header string from cURL response
     * @param array $json_args arguments to pass to json_decode function
     * @throws \JsonException
     */
    public function __construct($code, $raw_body, $headers, $json_args = array())
    {
        $this->code     = $code;
        $this->headers  = $this->parseHeaders($headers);
        $this->raw_body = $raw_body;
        $this->body     = $raw_body;

        // make sure raw_body is the first argument

        try {
            array_unshift($json_args, $raw_body);
            if(is_array($json_args)){
                $json_args = json_encode($json_args, JSON_THROW_ON_ERROR);
            }
            if (function_exists('json_decode')) {
                $json = json_decode($json_args, true, 512, JSON_THROW_ON_ERROR);

                if (json_last_error() === JSON_ERROR_NONE) {
                    $this->body = $json;
                }
            }
        }catch (Exception $e) {
            $this->body = json_encode($raw_body, JSON_THROW_ON_ERROR);
        }
    }

    /**
     * if PECL_HTTP is not available use a fall back function
     *
     * thanks to ricardovermeltfoort@gmail.com
     * http://php.net/manual/en/function.http-parse-headers.php#112986
     * @param string $raw_headers raw headers
     * @return array
     */
    private function parseHeaders($raw_headers)
    {
        if (function_exists('http_parse_headers')) {
            return http_parse_headers($raw_headers);
        }

        $key = '';
        $headers = array();

        foreach (explode("\n", $raw_headers) as $i => $h) {
            $h = explode(':', $h, 2);

            if (isset($h[1])) {
                if (!isset($headers[$h[0]])) {
                    $headers[$h[0]] = trim($h[1]);
                } elseif (is_array($headers[$h[0]])) {
                    $headers[$h[0]] = array_merge($headers[$h[0]], array(trim($h[1])));
                } else {
                    $headers[$h[0]] = [$headers[$h[0]], trim($h[1])];
                }

                $key = $h[0];
            } else {
                if (strpos($h[0], "\t") === 0) {
                    $headers[$key] .= "\r\n\t".trim($h[0]);
                } elseif (!$key) {
                    $headers[0] = trim($h[0]);
                }
            }
        }

        return $headers;
    }

    /**
     * @return int
     */
    public function getCode(): int
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getRawBody(): string
    {
        return $this->raw_body;
    }

    /**
     * @return false|mixed|string
     */
    public function getBody()
    {
        return $this->raw_body;
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }


    public function getProtocolVersion()
    {
        // TODO: Implement getProtocolVersion() method.
    }

    public function withProtocolVersion($version)
    {
        // TODO: Implement withProtocolVersion() method.
    }

    public function hasHeader($name)
    {
        // TODO: Implement hasHeader() method.
    }

    public function getHeader($name)
    {
        // TODO: Implement getHeader() method.
    }

    public function getHeaderLine($name)
    {
        // TODO: Implement getHeaderLine() method.
    }

    public function withHeader($name, $value)
    {
        // TODO: Implement withHeader() method.
    }

    public function withAddedHeader($name, $value)
    {
        // TODO: Implement withAddedHeader() method.
    }

    public function withoutHeader($name)
    {
        // TODO: Implement withoutHeader() method.
    }

    public function withBody(StreamInterface $body)
    {
        // TODO: Implement withBody() method.
    }

    public function getStatusCode()
    {
        return $this->code;
    }

    public function withStatus($code, $reasonPhrase = '')
    {
        // TODO: Implement withStatus() method.
    }

    public function getReasonPhrase()
    {
        // TODO: Implement getReasonPhrase() method.
    }


}