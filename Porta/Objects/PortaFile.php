<?php

/*
 * PortaOne Billing objects helper library
 * API docs: https://docs.portaone.com
 * (c) Alexey Pavlyuts <alexey@pavlyuts.ru>
 */

namespace Porta\Objects;

use \Psr\Http\Message\StreamInterface;

/**
 * Object to carry a file, returned by Billing
 *
 */
class PortaFile {

    protected string $name;
    protected StreamInterface $stream;

    public function __construct(string $filename, StreamInterface $stream) {
        $this->name = $filename;
        $this->stream = $stream;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getStream(): StreamInterface {
        return $this->stream;
    }

    public function __toString() {
        return (string) $this->stream;
    }

}
