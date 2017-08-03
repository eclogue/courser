<?php
/**
 * @license https://github.com/racecourse/courser/license.md
 * @copyright Copyright (c) 2017
 * @author: bugbear
 * @date: 2017/6/30
 * @time: 下午9:12
 */

namespace Courser\Http;

use Psr\Http\Message\UriInterface;

/**
 * Value object representing a URI.code refer slim https://github.com/slimphp/Slim/blob/3.x/Slim/Http/Uri.php
 *
 * Typically the Host header will be also be present in the request message.
 * For server-side requests, the scheme will typically be discoverable in the
 * server parameters.
 *
 * @link http://tools.ietf.org/html/rfc3986 (the URI specification)
 */
class Uri implements UriInterface
{

    protected $host;

    protected $port;

    protected $protocolVersion = 'http/1.1';

    protected $scheme = '';

    protected $path = '/';

    protected $fragment = '';

    protected $ip = '';

    protected $user = '';

    protected $password = '';

    protected $query = '';


    public function __construct($server)
    {
        $server = array_change_key_case($server, CASE_LOWER);
        $this->ip = $server['remote_addr'] ?? '';
        $this->protocolVersion = $server['server_protocol'] ?? $this->protocolVersion;
        $this->path = $server['request_uri'] ?? '/';
        $this->fragment = $server['fragment'] ?? '';
        $this->scheme = $server['https'] ?? 'http';
        $this->user = $server['user'] ?? '';
        $this->password = $server['password'] ?? '';
        $this->query = $server['query_string'] ?? '';
        $this->port = $server['server_port'] ?? '';
        if (isset($server['http_host'])) {
            $pos = strpos($server['http_host'], ':');
            if ($pos !== false) {
                list($host, $port) = explode(':', $server['http_host']);
                $this->host = $host;
                if (!$this->port) {
                    $this->port = $port;
                }
            } else {
                $this->host = $server['http_host'];
            }
        } else if($this->ip) {
            $this->host = $this->ip;
        }
    }


    /**
     * Retrieve the scheme component of the URI.
     *
     * @see https://tools.ietf.org/html/rfc3986#section-3.1
     * @return string The URI scheme.
     */
    public function getScheme()
    {
        return $this->scheme;
    }

    /**
     * Retrieve the authority component of the URI. refer SlimFramework
     *
     * @see https://tools.ietf.org/html/rfc3986#section-3.2
     * @return string The URI authority, in "[user-info@]host[:port]" format.
     */
    public function getAuthority()
    {
        $userInfo = $this->getUserInfo();
        $host = $this->getHost();
        $port = $this->getPort();
        return ($userInfo ? $userInfo . '@' : '') . $host . ($port !== null ? ':' . $port : '');

    }

    /**
     * Retrieve the user information component of the URI.
     * The trailing "@" character is not part of the user information and MUST
     * NOT be added.
     *
     * @return string The URI user information, in "username[:password]" format.
     */
    public function getUserInfo()
    {
        return $this->user . ($this->password ? ':' . $this->password : '');
    }

    /**
     * Retrieve the host component of the URI.
     *
     * If no host is present, this method MUST return an empty string.
     *
     * @see http://tools.ietf.org/html/rfc3986#section-3.2.2
     * @return string The URI host.
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Retrieve the port component of the URI.
     *
     * If no port is present, but a scheme is present, this method MAY return
     * the standard port for that scheme, but SHOULD return null.
     *
     * @return null|int The URI port.
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * Retrieve the path component of the URI.
     *
     * @see https://tools.ietf.org/html/rfc3986#section-2
     * @see https://tools.ietf.org/html/rfc3986#section-3.3
     * @return string The URI path.
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Retrieve the query string of the URI.
     *
     * @see https://tools.ietf.org/html/rfc3986#section-2
     * @see https://tools.ietf.org/html/rfc3986#section-3.4
     * @return string The URI query string.
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * Retrieve the fragment component of the URI.
     *
     * @see https://tools.ietf.org/html/rfc3986#section-2
     * @see https://tools.ietf.org/html/rfc3986#section-3.5
     * @return string The URI fragment.
     */
    public function getFragment()
    {
        return $this->fragment;
    }

    /**
     * Return an instance with the specified scheme.
     *
     * @param string $scheme The scheme to use with the new instance.
     * @return static A new instance with the specified scheme.
     * @throws \InvalidArgumentException for invalid or unsupported schemes.
     */
    public function withScheme($scheme)
    {
        $this->scheme = $scheme;
        return $this;
    }

    /**
     * Return an instance with the specified user information.
     *
     * @param string $user The user name to use for authority.
     * @param null|string $password The password associated with $user.
     * @return static A new instance with the specified user information.
     */
    public function withUserInfo($user, $password = null)
    {
        $this->user = $user;
        $this->password = $password;
        return $this;
    }

    /**
     * Return an instance with the specified host.
     *
     * @param string $host The hostname to use with the new instance.
     * @return static A new instance with the specified host.
     * @throws \InvalidArgumentException for invalid hostnames.
     */
    public function withHost($host)
    {
        $this->host = $host;
        return $this;
    }

    /**
     * Return an instance with the specified port.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified port.
     *
     * Implementations MUST raise an exception for ports outside the
     * established TCP and UDP port ranges.
     *
     * A null value provided for the port is equivalent to removing the port
     * information.
     *
     * @param null|int $port The port to use with the new instance; a null value
     *     removes the port information.
     * @return static A new instance with the specified port.
     * @throws \InvalidArgumentException for invalid ports.
     */
    public function withPort($port)
    {

    }

    /**
     * Return an instance with the specified path.
     *
     * @param string $path The path to use with the new instance.
     * @return static A new instance with the specified path.
     * @throws \InvalidArgumentException for invalid paths.
     */
    public function withPath($path)
    {
        $this->path = $path;
        return $this;
    }

    /**
     * Return an instance with the specified query string.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified query string.
     *
     * Users can provide both encoded and decoded query characters.
     * Implementations ensure the correct encoding as outlined in getQuery().
     *
     * An empty query string value is equivalent to removing the query string.
     *
     * @param string $query The query string to use with the new instance.
     * @return static A new instance with the specified query string.
     * @throws \InvalidArgumentException for invalid query strings.
     */
    public function withQuery($query)
    {
        $this->query = urlencode($query);
    }

    /**
     * Return an instance with the specified URI fragment.
     *
     * @param string $fragment The fragment to use with the new instance.
     * @return static A new instance with the specified fragment.
     */
    public function withFragment($fragment)
    {
        $this->fragment = $fragment;
        return $this;
    }

    public function getBaseUrl()
    {
        $url = [
            $this->scheme,
            '://',
            $this->getAuthority(),
            $this->host,
            ':',
            $this->port,
        ];
        return implode('', $url);
    }

    /**
     * Return the string representation as a URI reference.
     *
     * @see http://tools.ietf.org/html/rfc3986#section-4.1
     * @return string
     */
    public function __toString()
    {
        $compose = [];
        $scheme = $this->getScheme();
        $authority = $this->getAuthority();
        $path = $this->getPath();
        $query = $this->getQuery();
        $fragment = $this->getFragment();
        $scheme = $scheme ? $scheme . ':' : '';
        $compose[] = $scheme;
        $authority = $authority ? '//' . $authority : '';
        $compose[] = $authority;
        $compose[] = $this->host;
        $compose[] = ':';
        $compose[] = $this->port;
        $compose[] = $path;
        $query = $query ? '?' . $query : '';
        $compose[] = $query;
        $fragment = $fragment ? '#' . $fragment : '';
        $compose[] = $fragment;
        return implode('', $compose);
    }
}
