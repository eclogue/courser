<?php
/**
 * @license MIT
 * @copyright Copyright (c) 2018
 * @author: bugbear
 * @date: 2018/2/28
 * @time: 上午11:26
 */

namespace Courser;


use Psr\Http\Message\ResponseInterface;
use Hayrick\Environment\ReplyInterface;

class Terminator implements ReplyInterface
{
    protected $origin;

    public function __construct($origin = null)
    {
        $this->origin = $origin;
    }

    public function end(ResponseInterface $response)
    {
        if (!headers_sent()) {
            header(sprintf(
                'HTTP/%s %s %s',
                $response->getProtocolVersion(),
                $response->getStatusCode(),
                $response->getReasonPhrase()
            ));

            foreach ($response->getHeaders() as $name => $values) {
                if (is_array($values)) {
                    foreach ($values as $value) {
                        header(sprintf('%s: %s', $name, $value), false);
                    }
                } else {
                    header(sprintf('%s: %s', $name, $values), false);
                }
            }
        }

        if (!in_array($response->getStatusCode(), [204, 205, 304])) {
            $body = $response->getBody();
            if ($body->isSeekable()) {
                $body->rewind();
            }

            $chunkSize = 4096;
            $contentLength  = $response->getHeaderLine('Content-Length');
            if (!$contentLength) {
                $contentLength = $body->getSize();
            }

            if (isset($contentLength)) {
                $amountToRead = $contentLength;
                while ($amountToRead > 0 && !$body->eof()) {
                    $data = $body->read(min($chunkSize, $amountToRead));
                    echo $data;
                    $amountToRead -= strlen($data);
                    if (connection_status() !== CONNECTION_NORMAL) {
                        break;
                    }
                }
            } else {
                while (!$body->eof()) {
                    echo $body->read($chunkSize);
                    if (connection_status() !== CONNECTION_NORMAL) {
                        break;
                    }
                }
            }
        }
    }
}