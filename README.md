# PHP HTTPS Request Library

[![License](https://img.shields.io/badge/License-Apache%202.0-blue.svg)](https://opensource.org/licenses/Apache-2.0)
[![PHP Version](https://img.shields.io/badge/PHP-%3E%3D7.4-8892BF.svg)](https://www.php.net/)
[![PSR-7](https://img.shields.io/badge/PSR--7-compliant-green.svg)](https://www.php-fig.org/psr/psr-7/)

A powerful, lightweight PHP library for making HTTP/HTTPS requests with a simple, elegant API. Built on top of cURL with full PSR-7 compliance, security-first design, and comprehensive error handling.

**Developed by [Broos Action](https://broos.io)** - Your trusted partner for API development, cloud services, and digital transformation solutions.

---

## 📋 Table of Contents

- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Quick Start](#quick-start)
- [Basic Usage](#basic-usage)
- [Advanced Features](#advanced-features)
- [API Reference](#api-reference)
- [Security](#security)
- [Error Handling](#error-handling)
- [Contributing](#contributing)
- [Support](#support)
- [License](#license)

---

## ✨ Features

- 🔒 **Secure by Default** - SSL verification enabled, includes CA certificate bundle
- 🚀 **Simple & Elegant API** - Intuitive static methods for all HTTP operations
- 📦 **PSR-7 Compliant** - Full implementation of PSR-7 HTTP message interfaces
- 🔄 **Automatic JSON Handling** - Seamless JSON encoding/decoding
- 🌐 **Comprehensive HTTP Support** - All standard methods plus WebDAV extensions
- 🔐 **Authentication Built-in** - Basic, Digest, NTLM authentication support
- 🔌 **Proxy Support** - HTTP, SOCKS4, SOCKS5 proxy configuration
- 🍪 **Cookie Management** - Persistent cookie storage and handling
- 📤 **File Uploads** - Easy multipart file upload support
- 🎯 **UTF-8 URL Handling** - Proper internationalized domain name (IDN) support
- ⚡ **Performance Optimized** - Efficient request handling with cURL
- 📊 **Detailed Response Info** - Access to all cURL transfer information

---

## 📋 Requirements

- **PHP**: >= 7.4
- **Extensions**:
  - `ext-curl` (required)
  - `ext-json` (required)
  - `ext-pcre` (required)
  - `ext-openssl` (recommended for HTTPS)

---

## 📥 Installation

### Via Composer (Recommended)

```bash
composer require broosaction/php-https
```

### Manual Installation

1. Download the library
2. Include the autoloader in your project:

```php
require_once 'vendor/autoload.php';
```

---

## 🚀 Quick Start

```php
<?php

use Https\Target\Request;
use Https\Target\Request\Body;

// Simple GET request
$response = Request::get('https://api.example.com/users');
echo $response->code;        // 200
print_r($response->body);    // Parsed response body

// POST with JSON
$headers = ['Content-Type' => 'application/json'];
$body = Body::Json(['name' => 'John Doe', 'email' => 'john@example.com']);
$response = Request::post('https://api.example.com/users', $headers, $body);

// Check response
if ($response->code === 200) {
    echo "Success!";
}
```

---

## 📖 Basic Usage

### Making GET Requests

```php
use Https\Target\Request;

// Simple GET
$response = Request::get('https://api.example.com/data');

// GET with headers
$headers = [
    'Authorization' => 'Bearer YOUR_TOKEN',
    'Accept' => 'application/json'
];
$response = Request::get('https://api.example.com/data', $headers);

// GET with query parameters
$params = ['page' => 1, 'limit' => 10];
$response = Request::get('https://api.example.com/data', [], $params);
```

### Making POST Requests

```php
use Https\Target\Request;
use Https\Target\Request\Body;

// POST with JSON body
$headers = [
    'Authorization' => 'Bearer YOUR_API_KEY',
    'Content-Type' => 'application/json'
];

$body = Body::Json([
    'transactionName' => 'Payment',
    'amount' => 100.00,
    'currency' => 'USD',
    'customerEmail' => 'customer@example.com',
    'customerPhone' => '+1234567890',
    'metadata' => ['order_id' => '12345']
]);

$response = Request::post('https://api.example.com/payments', $headers, $body);

// Parse response
$result = json_decode($response->getRawBody(), true);
```

### Making PUT/PATCH Requests

```php
// Update resource with PUT
$body = Body::Json(['name' => 'Updated Name']);
$response = Request::put('https://api.example.com/users/123', $headers, $body);

// Partial update with PATCH
$body = Body::Json(['status' => 'active']);
$response = Request::patch('https://api.example.com/users/123', $headers, $body);
```

### Making DELETE Requests

```php
$headers = ['Authorization' => 'Bearer YOUR_TOKEN'];
$response = Request::delete('https://api.example.com/users/123', $headers);
```

### Other HTTP Methods

```php
// HEAD request (get headers only)
$response = Request::head('https://api.example.com/resource');

// OPTIONS request
$response = Request::options('https://api.example.com/resource');

// TRACE request
$response = Request::trace('https://api.example.com/resource');
```

---

## 🔥 Advanced Features

### Authentication

#### Basic Authentication

```php
use Https\Target\Request;

// Global authentication (applies to all requests)
Request::auth('username', 'password', CURLAUTH_BASIC);

$response = Request::get('https://api.example.com/protected');

// Or inline authentication
$response = Request::get(
    'https://api.example.com/protected',
    [],
    null,
    'username',
    'password'
);
```

#### Other Authentication Methods

```php
// Digest Authentication
Request::auth('username', 'password', CURLAUTH_DIGEST);

// NTLM Authentication
Request::auth('username', 'password', CURLAUTH_NTLM);

// Negotiate Authentication
Request::auth('username', 'password', CURLAUTH_NEGOTIATE);
```

### Proxy Configuration

```php
use Https\Target\Request;

// HTTP Proxy
Request::proxy('proxy.example.com', 8080, CURLPROXY_HTTP);

// SOCKS5 Proxy
Request::proxy('127.0.0.1', 1080, CURLPROXY_SOCKS5);

// Proxy with authentication
Request::proxy('proxy.example.com', 8080);
Request::proxyAuth('proxy_user', 'proxy_pass', CURLAUTH_BASIC);

// Enable tunneling
Request::proxy('proxy.example.com', 8080, CURLPROXY_HTTP, true);
```

### SSL/TLS Configuration

```php
use Https\Target\Request;

// Disable SSL verification (not recommended for production)
Request::verifyPeer(false);
Request::verifyHost(false);

// Enable SSL verification (default, recommended)
Request::verifyPeer(true);
Request::verifyHost(true);
```

⚠️ **Security Warning**: Only disable SSL verification in development environments. Always enable it in production.

### Timeout Configuration

```php
// Set timeout (in seconds)
Request::timeout(30);  // 30 seconds

// Make request with timeout
$response = Request::get('https://slow-api.example.com/data');
```

### Default Headers

```php
// Set default headers for all requests
Request::defaultHeaders([
    'User-Agent' => 'MyApp/1.0',
    'Accept' => 'application/json',
    'X-API-Version' => 'v2'
]);

// Add a single default header
Request::defaultHeader('X-Custom-Header', 'value');

// Clear all default headers
Request::clearDefaultHeaders();
```

### Custom cURL Options

```php
// Set custom cURL options
Request::curlOpts([
    CURLOPT_ENCODING => 'gzip',
    CURLOPT_VERBOSE => true,
    CURLOPT_CONNECTTIMEOUT => 10
]);

// Set single cURL option
Request::curlOpt(CURLOPT_FOLLOWLOCATION, false);

// Clear custom options
Request::clearCurlOpts();
```

### Cookie Management

```php
// Use cookie string
Request::cookie('session=abc123; token=xyz789');

// Use cookie file for persistence
Request::cookieFile('/tmp/cookies.txt');

// Cookies will be saved and loaded automatically
$response = Request::get('https://example.com/login');
```

### File Uploads

```php
use Https\Target\Request\Body;

// Single file upload
$body = Body::Multipart([
    'document' => Body::File('/path/to/file.pdf', 'application/pdf'),
    'description' => 'My document'
]);

$response = Request::post('https://api.example.com/upload', [], $body);

// Multiple files with metadata
$files = [
    'file1' => '/path/to/image.jpg',
    'file2' => '/path/to/document.pdf'
];

$data = [
    'title' => 'My Upload',
    'category' => 'documents'
];

$body = Body::Multipart($data, $files);
$response = Request::post('https://api.example.com/upload', [], $body);
```

### Form Data

```php
use Https\Target\Request\Body;

// URL-encoded form data
$formData = Body::Form([
    'username' => 'john',
    'password' => 'secret',
    'remember' => true
]);

$headers = ['Content-Type' => 'application/x-www-form-urlencoded'];
$response = Request::post('https://example.com/login', $headers, $formData);
```

### JSON Options

```php
// Configure JSON decoding
Request::jsonOpts(
    true,   // Associative arrays (instead of objects)
    512,    // Recursion depth
    JSON_BIGINT_AS_STRING  // Options
);

$response = Request::get('https://api.example.com/data');
// Response body will be decoded as associative array
```

---

## 📚 API Reference

### Request Methods

| Method | Description | Signature |
|--------|-------------|-----------|
| `get()` | Send GET request | `get(string $url, array $headers = [], $parameters = null)` |
| `post()` | Send POST request | `post(string $url, array $headers = [], $body = null)` |
| `put()` | Send PUT request | `put(string $url, array $headers = [], $body = null)` |
| `patch()` | Send PATCH request | `patch(string $url, array $headers = [], $body = null)` |
| `delete()` | Send DELETE request | `delete(string $url, array $headers = [], $body = null)` |
| `head()` | Send HEAD request | `head(string $url, array $headers = [], $parameters = null)` |
| `options()` | Send OPTIONS request | `options(string $url, array $headers = [], $parameters = null)` |

### Configuration Methods

| Method | Description | Signature |
|--------|-------------|-----------|
| `auth()` | Set authentication | `auth(string $username, string $password, int $method = CURLAUTH_BASIC)` |
| `proxy()` | Configure proxy | `proxy(string $address, int $port = 1080, int $type = CURLPROXY_HTTP, bool $tunnel = false)` |
| `proxyAuth()` | Set proxy authentication | `proxyAuth(string $username, string $password, int $method = CURLAUTH_BASIC)` |
| `verifyPeer()` | Enable/disable SSL peer verification | `verifyPeer(bool $enabled)` |
| `verifyHost()` | Enable/disable SSL host verification | `verifyHost(bool $enabled)` |
| `timeout()` | Set request timeout | `timeout(int $seconds)` |
| `defaultHeaders()` | Set default headers | `defaultHeaders(array $headers)` |
| `defaultHeader()` | Set single default header | `defaultHeader(string $name, string $value)` |
| `curlOpts()` | Set custom cURL options | `curlOpts(array $options)` |
| `cookie()` | Set cookie string | `cookie(string $cookie)` |
| `cookieFile()` | Set cookie file path | `cookieFile(string $cookieFile)` |

### Response Properties

| Property | Type | Description |
|----------|------|-------------|
| `$code` | `int` | HTTP status code |
| `$body` | `mixed` | Parsed response body (auto-decoded JSON if applicable) |
| `$raw_body` | `string` | Raw response body |
| `$headers` | `array` | Response headers |

### Response Methods

| Method | Description | Return Type |
|--------|-------------|-------------|
| `getCode()` | Get HTTP status code | `int` |
| `getRawBody()` | Get raw response body | `string` |
| `getBody()` | Get parsed response body | `mixed` |
| `getHeaders()` | Get all response headers | `array` |
| `getHeader($name)` | Get specific header | `mixed` |
| `hasHeader($name)` | Check if header exists | `bool` |
| `getStatusCode()` | Get status code (PSR-7) | `int` |
| `getReasonPhrase()` | Get HTTP reason phrase | `string` |

### Body Helper Methods

| Method | Description | Signature |
|--------|-------------|-----------|
| `Body::Json()` | Create JSON body | `Json($data)` |
| `Body::Form()` | Create form-encoded body | `Form($data)` |
| `Body::Multipart()` | Create multipart body | `Multipart($data, $files = false)` |
| `Body::File()` | Prepare file for upload | `File(string $filename, string $mimetype = '', string $postname = '')` |

---

## 🔒 Security

### Best Practices

1. **Always use HTTPS in production**
   ```php
   // Good
   $response = Request::get('https://api.example.com/data');
   
   // Avoid in production
   $response = Request::get('http://api.example.com/data');
   ```

2. **Keep SSL verification enabled**
   ```php
   // Default (recommended)
   Request::verifyPeer(true);
   Request::verifyHost(true);
   ```

3. **Secure credential storage**
   ```php
   // Use environment variables
   Request::auth(
       getenv('API_USERNAME'),
       getenv('API_PASSWORD')
   );
   ```

4. **Validate API responses**
   ```php
   $response = Request::get('https://api.example.com/data');
   
   if ($response->code !== 200) {
       throw new Exception("API request failed: " . $response->getReasonPhrase());
   }
   ```

5. **Set appropriate timeouts**
   ```php
   // Prevent hanging requests
   Request::timeout(30);
   ```

---

## ⚠️ Error Handling

### Basic Error Handling

```php
use Https\Target\Request;
use Https\Target\Exception;

try {
    $response = Request::get('https://api.example.com/data');
    
    if ($response->code >= 400) {
        throw new Exception("HTTP Error: {$response->code} - {$response->getReasonPhrase()}");
    }
    
    // Process successful response
    $data = json_decode($response->getRawBody(), true);
    
} catch (Exception $e) {
    // Handle cURL errors
    error_log("Request failed: " . $e->getMessage());
}
```

### Advanced Error Handling

```php
use Https\Target\Request;

try {
    Request::timeout(10);
    $response = Request::post('https://api.example.com/payment', $headers, $body);
    
    switch ($response->code) {
        case 200:
        case 201:
            // Success
            $result = json_decode($response->getRawBody(), true);
            break;
            
        case 400:
            // Bad request
            throw new \InvalidArgumentException("Invalid request parameters");
            
        case 401:
            // Unauthorized
            throw new \RuntimeException("Authentication required");
            
        case 403:
            // Forbidden
            throw new \RuntimeException("Access denied");
            
        case 404:
            // Not found
            throw new \RuntimeException("Resource not found");
            
        case 429:
            // Rate limited
            throw new \RuntimeException("Rate limit exceeded");
            
        case 500:
        case 502:
        case 503:
            // Server errors
            throw new \RuntimeException("Server error, please try again later");
            
        default:
            throw new \RuntimeException("Unexpected HTTP status: {$response->code}");
    }
    
} catch (\Https\Target\Exception $e) {
    // cURL or network error
    error_log("Network error: " . $e->getMessage());
} catch (\Exception $e) {
    // Application error
    error_log("Application error: " . $e->getMessage());
}
```

### Getting Response Explanations

```php
$response = Request::get('https://api.example.com/data');

// Get explanation for status code
$explanation = $response->getResponseCodeExplanation($response->code);

echo "Status: {$explanation['meaning']}\n";
echo "Cause: {$explanation['cause']}\n";
echo "Next Actions: " . implode(', ', $explanation['next_actions']);
```

---

## 💡 Real-World Examples

### RESTful API Integration

```php
<?php

use Https\Target\Request;
use Https\Target\Request\Body;

class PaymentGateway
{
    private $apiKey;
    private $baseUrl;
    
    public function __construct(string $apiKey, string $baseUrl)
    {
        $this->apiKey = $apiKey;
        $this->baseUrl = $baseUrl;
        
        // Configure default settings
        Request::timeout(30);
        Request::verifyPeer(true);
        Request::defaultHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ]);
    }
    
    public function createPayment(array $data): array
    {
        $body = Body::Json([
            'amount' => $data['amount'],
            'currency' => $data['currency'],
            'customerEmail' => $data['email'],
            'reference' => $data['reference'],
            'metadata' => $data['metadata'] ?? []
        ]);
        
        $response = Request::post($this->baseUrl . '/payments', [], $body);
        
        if ($response->code !== 201) {
            throw new \RuntimeException("Payment creation failed: {$response->getRawBody()}");
        }
        
        return json_decode($response->getRawBody(), true);
    }
    
    public function getPayment(string $id): array
    {
        $response = Request::get($this->baseUrl . '/payments/' . $id);
        
        if ($response->code !== 200) {
            throw new \RuntimeException("Payment retrieval failed");
        }
        
        return json_decode($response->getRawBody(), true);
    }
}

// Usage
$gateway = new PaymentGateway('your-api-key', 'https://api.payment.com');

$payment = $gateway->createPayment([
    'amount' => 100.00,
    'currency' => 'USD',
    'email' => 'customer@example.com',
    'reference' => 'ORDER-12345'
]);
```

### Webhook Handler

```php
<?php

use Https\Target\Request;
use Https\Target\Request\Body;

class WebhookNotifier
{
    public static function notify(string $webhookUrl, array $data): bool
    {
        $headers = [
            'Content-Type' => 'application/json',
            'X-Webhook-Signature' => hash_hmac('sha256', json_encode($data), 'secret')
        ];
        
        $body = Body::Json($data);
        
        try {
            Request::timeout(10);
            $response = Request::post($webhookUrl, $headers, $body);
            
            return $response->code >= 200 && $response->code < 300;
        } catch (\Exception $e) {
            error_log("Webhook delivery failed: " . $e->getMessage());
            return false;
        }
    }
}
```

### File Download

```php
<?php

use Https\Target\Request;

function downloadFile(string $url, string $savePath): bool
{
    try {
        Request::timeout(300); // 5 minutes for large files
        $response = Request::get($url);
        
        if ($response->code !== 200) {
            return false;
        }
        
        return file_put_contents($savePath, $response->getRawBody()) !== false;
    } catch (\Exception $e) {
        error_log("Download failed: " . $e->getMessage());
        return false;
    }
}

// Usage
downloadFile('https://example.com/files/document.pdf', '/tmp/document.pdf');
```

---

## 🌐 Helpful Resources

### PHP & HTTP Documentation
- [PHP cURL Documentation](https://www.php.net/manual/en/book.curl.php)
- [HTTP Status Codes](https://developer.mozilla.org/en-US/docs/Web/HTTP/Status)
- [PSR-7: HTTP Message Interface](https://www.php-fig.org/psr/psr-7/)
- [RESTful API Best Practices](https://restfulapi.net/)

### Related Libraries
- [Guzzle HTTP Client](https://docs.guzzlephp.org/)
- [Symfony HTTP Client](https://symfony.com/doc/current/http_client.html)
- [PHP HTTP Foundation](https://symfony.com/doc/current/components/http_foundation.html)

### Security Resources
- [OWASP API Security](https://owasp.org/www-project-api-security/)
- [SSL/TLS Best Practices](https://wiki.mozilla.org/Security/Server_Side_TLS)

---

## 🤝 Contributing

We welcome contributions! Here's how you can help:

1. **Fork the repository**
2. **Create a feature branch**: `git checkout -b feature/amazing-feature`
3. **Commit your changes**: `git commit -m 'Add amazing feature'`
4. **Push to the branch**: `git push origin feature/amazing-feature`
5. **Open a Pull Request**

### Development Setup

```bash
# Clone repository
git clone https://github.com/broosaction/php-https.git
cd php-https

# Install dependencies
composer install

# Run tests
composer test
```

### Coding Standards

- Follow PSR-12 coding standards
- Write PHPDoc comments for all public methods
- Include unit tests for new features
- Update documentation for API changes

---

## 📞 Support

Need help? We're here for you!

### 🌟 Professional Support
For enterprise support, custom integrations, and consulting services, visit:
- **[Broos Action](https://broos.io)** - Professional software development and consulting
- **[Client Support Portal](https://broosaction.com/client-support/)** - 24/7 technical support

### 📧 Contact Information
- **Email**: [email protected]
- **Phone**: +260954922329
- **Average Response Time**: 3 minutes

### 🐛 Bug Reports
Found a bug? Please open an issue on [GitHub Issues](https://github.com/broosaction/php-https/issues)

### 💬 Community
- [GitHub Discussions](https://github.com/broosaction/php-https/discussions)
- [Stack Overflow](https://stackoverflow.com/questions/tagged/php-https) - Tag your questions with `php-https`

---

## 📄 License

This project is licensed under the Apache License 2.0 - see the [LICENSE](LICENSE) file for details.

```
Copyright 2025 Broos Action

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
```

---

## 🌟 About Broos Action

**[Broos Action](https://broos.io)** is a leading technology solutions provider specializing in:

- ☁️ **Cloud Services** - AWS, Google Cloud, Microsoft Azure
- 🔌 **API Development & Integration** - RESTful APIs, GraphQL, WebSockets
- 📱 **Mobile Development** - iOS, Android applications
- 🤖 **Artificial Intelligence** - Machine learning, AI integration
- 🔐 **Cybersecurity Solutions** - Security audits, compliance
- 📊 **Business Intelligence** - Data analytics and insights

### Industries We Serve
- Financial Services
- Healthcare & Life Sciences
- Manufacturing & Industrial
- Retail & E-commerce
- Government & Public Sector
- Technology & SaaS

**Trusted by businesses worldwide** with a proven track record of 3+ years, 98% customer satisfaction, and 3-minute average response time.

---

## 📊 Statistics

![GitHub stars](https://img.shields.io/github/stars/broosaction/php-https?style=social)
![GitHub forks](https://img.shields.io/github/forks/broosaction/php-https?style=social)
![GitHub issues](https://img.shields.io/github/issues/broosaction/php-https)
![GitHub pull requests](https://img.shields.io/github/issues-pr/broosaction/php-https)
![Packagist Downloads](https://img.shields.io/packagist/dt/broosaction/php-https)
![Packagist Version](https://img.shields.io/packagist/v/broosaction/php-https)

---

## 🙏 Acknowledgments

- Thanks to all [contributors](https://github.com/broosaction/php-https/graphs/contributors)
- Built with ❤️ by [Broos Action](https://broos.io)
- Special thanks to the PHP community

---

## 📝 Changelog

### Version 1.0.0 (Current)
- Initial release
- Full PSR-7 compliance
- Support for all HTTP methods
- Authentication and proxy support
- File upload capabilities
- Comprehensive error handling

For detailed changelog, see [CHANGELOG.md](CHANGELOG.md)

---

<div align="center">

**[Documentation](https://github.com/broosaction/php-https/wiki)** • 
**[API Reference](https://github.com/broosaction/php-https/blob/main/docs/api.md)** • 
**[Examples](https://github.com/broosaction/php-https/tree/main/examples)** • 
**[Support](https://broosaction.com/client-support/)**

Made with ❤️ by **[Broos Action](https://broos.io)**

⭐ Star us on GitHub — it motivates us a lot!

</div>

