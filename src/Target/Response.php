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
        $this->code = $code;
        $this->headers = $this->parseHeaders($headers);
        $this->raw_body = $raw_body;
        $this->body = $raw_body;

        // make sure raw_body is the first argument

        try {
            array_unshift($json_args, $raw_body);
            if (is_array($json_args)) {
                $json_args = json_encode($json_args, JSON_THROW_ON_ERROR);
            }
            if (function_exists('json_decode')) {
                $json = json_decode($json_args, true, 512, JSON_THROW_ON_ERROR);

                if (json_last_error() === JSON_ERROR_NONE) {
                    $this->body = $json;
                }
            }
        } catch (Exception $e) {
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
                    $headers[$key] .= "\r\n\t" . trim($h[0]);
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
        return isset($this->headers[$name]);
    }

    public function getHeader($name)
    {
        if (!$this->hasHeader($name)) {
            return [];
        }

        return $this->headers[$name];
    }

    public function getHeaderLine($name)
    {
        if (!$this->hasHeader($name)) {
            return '';
        }

        if (is_array($this->headers[$name])) {
            return implode(', ', $this->headers[$name]);
        }

        return $this->headers[$name];
    }

    public function withHeader($name, $value)
    {
        $newResponse = clone $this;
        $newResponse->headers[$name] = $value;
        return $newResponse;
    }

    public function withAddedHeader($name, $value)
    {
        $newResponse = clone $this;
        if (isset($newResponse->headers[$name])) {
            if (!is_array($newResponse->headers[$name])) {
                $newResponse->headers[$name] = [$newResponse->headers[$name]];
            }
            $newResponse->headers[$name][] = $value;
        } else {
            $newResponse->headers[$name] = $value;
        }
        return $newResponse;
    }

    public function withoutHeader($name)
    {
        $newResponse = clone $this;
        unset($newResponse->headers[$name]);
        return $newResponse;
    }

    public function withBody(StreamInterface $body)
    {
        $newResponse = clone $this;
        $newResponse->body = $body;
        return $newResponse;
    }

    public function getStatusCode()
    {
        return $this->code;
    }

    public function withStatus($code, $reasonPhrase = '')
    {
        // Creating a new instance of the response with the updated status code and reason phrase
        $newResponse = clone $this;
        $newResponse->code = $code;
        // Optional: you can set the reason phrase here if needed
        // $newResponse->reasonPhrase = $reasonPhrase;
        return $newResponse;
    }

    public function getReasonPhrase()
    {
        //a list of common HTTP status codes and their corresponding reason phrases
        $reasonPhrases = [
            100 => 'Continue',
            101 => 'Switching Protocols',
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Found',
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            307 => 'Temporary Redirect',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Timeout',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Payload Too Large',
            414 => 'URI Too Long',
            415 => 'Unsupported Media Type',
            416 => 'Range Not Satisfiable',
            417 => 'Expectation Failed',
            418 => 'I\'m a teapot',
            421 => 'Misdirected Request',
            422 => 'Unprocessable Entity',
            423 => 'Locked',
            424 => 'Failed Dependency',
            426 => 'Upgrade Required',
            428 => 'Precondition Required',
            429 => 'Too Many Requests',
            431 => 'Request Header Fields Too Large',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported',
            506 => 'Variant Also Negotiates',
            507 => 'Insufficient Storage',
            508 => 'Loop Detected',
            510 => 'Not Extended',
            511 => 'Network Authentication Required',
        ];

        // Check if the response code exists in the list of reason phrases
        if (isset($reasonPhrases[(int)$this->code])) {
            return $reasonPhrases[(int)$this->code];
        } else {
            // If the response code is not found in the list, return an empty string
            return '';
        }
    }


    public function getResponseCodeExplanation($code)
    {
        $explanations = [
            100 => [
                'meaning' => 'Continue',
                'cause' => 'The server has received the request headers and the client should proceed to send the request body.',
                'next_actions' => ['Continue sending the request body if needed.'],
            ],
            101 => [
                'meaning' => 'Switching Protocols',
                'cause' => 'The server is willing to change the application protocol being used on this connection following a client request.',
                'next_actions' => ['Switch to the new protocol as indicated in the response.'],
            ],
            200 => [
                'meaning' => 'OK',
                'cause' => 'The request has succeeded. The information returned with the response is dependent on the method used in the request.',
                'next_actions' => ['Process the response data as needed.'],
            ],
            201 => [
                'meaning' => 'Created',
                'cause' => 'The request has been fulfilled and has resulted in one or more new resources being created.',
                'next_actions' => ['Inspect the response headers for information about the newly created resource(s).'],
            ],
            202 => [
                'meaning' => 'Accepted',
                'cause' => 'The request has been accepted for processing, but the processing has not been completed.',
                'next_actions' => ['Monitor the status of the request for completion.'],
            ],
            204 => [
                'meaning' => 'No Content',
                'cause' => 'The server successfully processed the request and is not returning any content.',
                'next_actions' => ['Proceed with the next action as required.'],
            ],
            206 => [
                'meaning' => 'Partial Content',
                'cause' => 'The server is delivering only part of the resource due to a range header sent by the client.',
                'next_actions' => ['Process the partial content as needed.'],
            ],
            300 => [
                'meaning' => 'Multiple Choices',
                'cause' => 'The request has more than one possible response. The user or user agent should choose one of them.',
                'next_actions' => ['Present the user with a list of choices and let them select one.'],
            ],
            301 => [
                'meaning' => 'Moved Permanently',
                'cause' => 'The resource has been moved permanently to a new location, and future references should use the new URI.',
                'next_actions' => ['Update all references to the resource to use the new URI.'],
            ],
            302 => [
                'meaning' => 'Found',
                'cause' => 'The resource requested has been temporarily moved to a different URI.',
                'next_actions' => ['Follow the redirect using the URI provided in the Location header.'],
            ],
            304 => [
                'meaning' => 'Not Modified',
                'cause' => 'Indicates that the resource has not been modified since the version specified by the If-Modified-Since or If-None-Match headers.',
                'next_actions' => ['Use the cached response as it is still valid.'],
            ],
            400 => [
                'meaning' => 'Bad Request',
                'cause' => 'The server cannot process the request due to a client error (e.g., malformed request syntax, invalid request message framing, or deceptive request routing).',
                'next_actions' => ['Check and correct the request parameters or syntax before resubmitting.'],
            ],
            401 => [
                'meaning' => 'Unauthorized',
                'cause' => 'The request requires user authentication. The response must include a WWW-Authenticate header field containing a challenge applicable to the requested resource.',
                'next_actions' => ['Provide valid credentials or authenticate the user before retrying the request.'],
            ],
            403 => [
                'meaning' => 'Forbidden',
                'cause' => 'The server understood the request, but it refuses to authorize it. Typically, this is because the user does not have the necessary permissions for the resource.',
                'next_actions' => ['Ensure that the user has the required permissions to access the resource.'],
            ],
            404 => [
                'meaning' => 'Not Found',
                'cause' => 'The server cannot find the requested resource. It may have been deleted or moved to a different location.',
                'next_actions' => ['Check the URI for typos or contact the server administrator for assistance.'],
            ],
            405 => [
                'meaning' => 'Method Not Allowed',
                'cause' => 'The method specified in the request line is known to the server but is not allowed for the requested resource.',
                'next_actions' => ['Use a different HTTP method or check the allowed methods for the resource.'],
            ],
            406 => [
                'meaning' => 'Not Acceptable',
                'cause' => 'The server cannot generate a response that the client can accept according to the Accept headers sent in the request.',
                'next_actions' => ['Adjust the Accept headers in the request or contact the server administrator for assistance.'],
            ],
            408 => [
                'meaning' => 'Request Timeout',
                'cause' => 'The client did not produce a request within the time that the server was prepared to wait.',
                'next_actions' => ['Retry the request or adjust the client-side timeout settings.'],
            ],
            409 => [
                'meaning' => 'Conflict',
                'cause' => 'Indicates that the request could not be completed due to a conflict with the current state of the resource.',
                'next_actions' => ['Resolve the conflict by either modifying the resource state or retrying the request after a delay.'],
            ],
            410 => [
                'meaning' => 'Gone',
                'cause' => 'The requested resource is no longer available at the server and no forwarding address is known.',
                'next_actions' => ['Update or remove references to the resource as it is permanently gone.'],
            ],
            411 => [
                'meaning' => 'Length Required',
                'cause' => 'The server refuses to accept the request without a defined Content-Length header.',
                'next_actions' => ['Ensure that the Content-Length header is provided in the request.'],
            ],
            413 => [
                'meaning' => 'Payload Too Large',
                'cause' => 'The server refuses to process the request because the payload is larger than the server is willing or able to process.',
                'next_actions' => ['Reduce the size of the payload or use chunked transfer encoding.'],
            ],
            414 => [
                'meaning' => 'URI Too Long',
                'cause' => 'The server refuses to process the request because the URI is longer than the server is willing to interpret.',
                'next_actions' => ['Shorten the URI or use a POST request instead of GET if applicable.'],
            ],
            415 => [
                'meaning' => 'Unsupported Media Type',
                'cause' => 'The server refuses to process the request because the request entity is in a format not supported by the server for the requested resource.',
                'next_actions' => ['Use a supported media type for the request entity or contact the server administrator for assistance.'],
            ],
            416 => [
                'meaning' => 'Range Not Satisfiable',
                'cause' => 'The client has asked for a portion of the file, but the server cannot supply that portion.',
                'next_actions' => ['Adjust the Range header in the request or request the entire resource.'],
            ],
            417 => [
                'meaning' => 'Expectation Failed',
                'cause' => 'The server cannot meet the requirements of the Expect request-header field.',
                'next_actions' => ['Remove the Expect header from the request or adjust its value.'],
            ],
            418 => [
                'meaning' => 'I\'m a teapot',
                'cause' => 'The server refuses to brew coffee because it is a teapot.',
                'next_actions' => ['Do not attempt to brew coffee with a teapot.'],
            ],
            421 => [
                'meaning' => 'Misdirected Request',
                'cause' => 'The request was directed at a server that is not able to produce a response.',
                'next_actions' => ['Review and correct the request destination or contact the server administrator for assistance.'],
            ],
            422 => [
                'meaning' => 'Unprocessable Entity',
                'cause' => 'The server understands the content type of the request entity, but it was unable to process the contained instructions.',
                'next_actions' => ['Correct the content of the request entity and resubmit the request.'],
            ],
            423 => [
                'meaning' => 'Locked',
                'cause' => 'The requested resource is currently locked and only available for reading.',
                'next_actions' => ['Wait for the resource to be unlocked or contact the server administrator for assistance.'],
            ],
            424 => [
                'meaning' => 'Failed Dependency',
                'cause' => 'The method could not be performed on the resource because the requested action depended on another action that failed.',
                'next_actions' => ['Resolve the dependencies and retry the request.'],
            ],
            426 => [
                'meaning' => 'Upgrade Required',
                'cause' => 'The server refuses to perform the request using the current protocol, but may do so after the client upgrades to a different protocol.',
                'next_actions' => ['Upgrade the client to the required protocol version and retry the request.'],
            ],
            428 => [
                'meaning' => 'Precondition Required',
                'cause' => 'The server requires the request to be conditional.',
                'next_actions' => ['Include the required preconditions in the request headers and retry the request.'],
            ],
            429 => [
                'meaning' => 'Too Many Requests',
                'cause' => 'The user has sent too many requests in a given amount of time ("rate limiting").',
                'next_actions' => ['Reduce the frequency of requests or contact the server administrator for assistance.'],
            ],
            431 => [
                'meaning' => 'Request Header Fields Too Large',
                'cause' => 'The server refuses to process the request because the request s headers are too large.',
                'next_actions' => ['Reduce the size of the request headers and retry the request.'],
            ],
            500 => [
                'meaning' => 'Internal Server Error',
                'cause' => 'A generic error message indicating that something has gone wrong on the server and the server could not be more specific about what the exact problem is.',
                'next_actions' => ['Contact the server administrator for assistance.'],
            ],
            501 => [
                'meaning' => 'Not Implemented',
                'cause' => 'The server does not support the functionality required to fulfill the request.',
                'next_actions' => ['Do not attempt the requested action or contact the server administrator for assistance.'],
            ],
            502 => [
                'meaning' => 'Bad Gateway',
                'cause' => 'The server, while acting as a gateway or proxy, received an invalid response from an inbound server it accessed while attempting to fulfill the request.',
                'next_actions' => ['Check the upstream servers for errors and try again later.'],
            ],
            503 => [
                'meaning' => 'Service Unavailable',
                'cause' => 'The server is currently unable to handle the request due to temporary overloading or maintenance of the server.',
                'next_actions' => ['Retry the request after some time or contact the server administrator for assistance.'],
            ],
            504 => [
                'meaning' => 'Gateway Timeout',
                'cause' => 'The server, while acting as a gateway or proxy, did not receive a timely response from an upstream server it needed to access in order to complete the request.',
                'next_actions' => ['Check the upstream servers for errors and try again later.'],
            ],
            505 => [
                'meaning' => 'HTTP Version Not Supported',
                'cause' => 'The server does not support, or refuses to support, the HTTP protocol version that was used in the request message.',
                'next_actions' => ['Use a different HTTP protocol version or contact the server administrator for assistance.'],
            ],
            506 => [
                'meaning' => 'Variant Also Negotiates',
                'cause' => 'The server has an internal configuration error: transparent content negotiation for the request results in a circular reference.',
                'next_actions' => ['Review and correct the server configuration or contact the server administrator for assistance.'],
            ],
            507 => [
                'meaning' => 'Insufficient Storage',
                'cause' => 'The server is unable to store the representation needed to complete the request.',
                'next_actions' => ['Free up storage space or contact the server administrator for assistance.'],
            ],
            508 => [
                'meaning' => 'Loop Detected',
                'cause' => 'The server detected an infinite loop while processing the request.',
                'next_actions' => ['Fix the server configuration to break the loop or contact the server administrator for assistance.'],
            ],
            510 => [
                'meaning' => 'Not Extended',
                'cause' => 'Further extensions to the request are required for the server to fulfill it.',
                'next_actions' => ['Provide the necessary extensions to the request and retry.'],
            ],
            511 => [
                'meaning' => 'Network Authentication Required',
                'cause' => 'The client needs to authenticate to gain network access.',
                'next_actions' => ['Provide the required authentication credentials and retry the request.'],
            ],
        ];

        // Check if the explanation for the given response code exists
        if (isset($explanations[$code])) {
            return $explanations[$code];
        } else {
            // If the explanation for the response code is not found, return an empty array
            return [];
        }
    }


}