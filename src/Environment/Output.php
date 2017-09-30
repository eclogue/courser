<?php
/**
 * @license MIT
 * @copyright Copyright (c) 2017
 * @author: bugbear
 * @date: 2017/9/28
 * @time: 下午8:31
 */

namespace Courser\Environment;

use InvalidArgumentException;

class Output
{

    protected $content = [];

    protected $header = [];

    protected $file = '';

    protected $finish = false;

    protected $status = 200;


    /**
     * set response header
     *
     * @param string $key
     * @param string $value
     * @return array
     */
    public function header(string $key, string $value): array
    {
        $this->header[$key] = $value;

        return $this->header;
    }

    /**
     * set response body data
     *
     * @param array $data
     * @return array
     */
    public function body(array $data): array
    {
        $this->content = array_merge($this->content, $data);

        return $this->content;
    }

    /**
     * set response file
     *
     * @param string $file
     * @return string
     */
    public function sendFile(string $file): string
    {
        $this->file = $file;

        return $this->file;
    }

    public function status(int $status = 200): int
    {
        $this->status = $status;

        return $this->status;
    }


    public function __toString()
    {
        return '';
    }

    public function getHeaders()
    {
        return $this->header;
    }

    public function getBody()
    {
        return implode('', $this->content);
    }
}