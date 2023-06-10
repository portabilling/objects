<?php

/*
 * PortaOne Billing objects helper library
 * API docs: https://docs.portaone.com
 * (c) Alexey Pavlyuts <alexey@pavlyuts.ru>
 */

namespace PortaObjectsTest;

/**
 *  Test case supporting test data load from files
 *
 */
class DataTestCase extends \PHPUnit\Framework\TestCase {

    protected function getFileName(string $name) {
        return __DIR__ . '/TestData/' . $name . '.json';
    }

    protected function load(string $name): array {
        return json_decode(file_get_contents($this->getFileName($name)), true);
    }

}
