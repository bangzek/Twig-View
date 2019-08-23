<?php
/**
 * Slim Framework (http://slimframework.com)
 *
 * @license   https://github.com/slimphp/Twig-View/blob/master/LICENSE.md (MIT License)
 */

declare(strict_types=1);

namespace Slim\Views;

use Psr\Http\Message\UriInterface;
use Slim\Interfaces\RouteParserInterface;

class TwigRuntimeExtension
{
    /**
     * Get the relative path of $to from $from path
     *
     * @param string $to    Destination path
     * @param string $from  Current path
     *
     * @return string
     */
    public static function relativePath(string $to, string $from): string
    {
        if ($from === $to) {
            return '';
        }

        // Remove common path
        if (preg_match("|^(.*/)(.*?)\t\\1(.*?)$|", "$to\t$from", $m)) {
            $to   = $m[2];
            $from = $m[3];
        }

        $depth = substr_count($from, '/');
        if ($depth) {
            $goback = str_repeat('../', $depth);
        } else {
            $goback = '';
        }

        if ($goback || $to) {
            return $goback.$to;
        }
        return './';
    }

    /**
     * @var RouteParserInterface
     */
    protected $routeParser;

    /**
     * @var string
     */
    protected $basePath = '';

    /**
     * @var UriInterface
     */
    protected $uri;

    /**
     * @param RouteParserInterface $routeParser Route parser
     * @param UriInterface         $uri         Uri
     * @param string               $basePath    Base path
     */
    public function __construct(RouteParserInterface $routeParser, UriInterface $uri, string $basePath = '')
    {
        $this->routeParser = $routeParser;
        $this->uri = $uri;
        $this->basePath = $basePath;
    }

    /**
     * Get the url for a named route
     *
     * @param string $routeName   Route name
     * @param array  $data        Route placeholders
     * @param array  $queryParams Query parameters
     *
     * @return string
     */
    public function urlFor(string $routeName, array $data = [], array $queryParams = []): string
    {
        return $this->routeParser->urlFor($routeName, $data, $queryParams);
    }

    /**
     * Get the url for a named route relatively to current path
     *
     * @param string $routeName   Route name
     * @param array  $data        Route placeholders
     * @param array  $queryParams Query parameters
     *
     * @return string
     */
    public function relativeUrlFor(string $routeName, array $data = [], array $queryParams = []): string
    {
        $path  = $this->urlFor($routeName, $data, $queryParams);
        $query = '';
        if (preg_match('/^([^?]+)([?].*)/', $path, $m)) {
            $path  = $m[1];
            $query = $m[2];
        }

        return self::relativePath($path, $this->getCurrentUrl()).$query;
    }

    /**
     * Get the full url for a named route
     *
     * @param string $routeName   Route name
     * @param array  $data        Route placeholders
     * @param array  $queryParams Query parameters
     *
     * @return string
     */
    public function fullUrlFor(string $routeName, array $data = [], array $queryParams = []): string
    {
        return $this->routeParser->fullUrlFor($this->uri, $routeName, $data, $queryParams);
    }

    /**
     * @param string $routeName Route name
     * @param array  $data      Route placeholders
     *
     * @return bool
     */
    public function isCurrentUrl(string $routeName, array $data = []): bool
    {
        $currentUrl = $this->basePath.$this->uri->getPath();
        $result = $this->routeParser->urlFor($routeName, $data);

        return $result === $currentUrl;
    }

    /**
     * Get current path on given Uri
     *
     * @param bool $withQueryString
     *
     * @return string
     */
    public function getCurrentUrl(bool $withQueryString = false): string
    {
        $currentUrl = $this->basePath.$this->uri->getPath();
        $query = $this->uri->getQuery();

        if ($withQueryString && !empty($query)) {
            $currentUrl .= '?'.$query;
        }

        return $currentUrl;
    }

    /**
     * Get the uri
     *
     * @return UriInterface
     */
    public function getUri(): UriInterface
    {
        return $this->uri;
    }

    /**
     * Set the uri
     *
     * @param UriInterface $uri
     *
     * @return self
     */
    public function setUri(UriInterface $uri): self
    {
        $this->uri = $uri;

        return $this;
    }

    /**
     * Get the base path
     *
     * @return string
     */
    public function getBasePath(): string
    {
        return $this->basePath;
    }

    /**
     * Set the base path
     *
     * @param string $basePath
     *
     * @return self
     */
    public function setBasePath(string $basePath): self
    {
        $this->basePath = $basePath;

        return $this;
    }
}
