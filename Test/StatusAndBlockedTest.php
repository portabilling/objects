<?php

/*
 * PortaOne Billing objects helper library
 * API docs: https://docs.portaone.com
 * (c) Alexey Pavlyuts <alexey@pavlyuts.ru>
 */

namespace PortaObjectsTest;

/**
 * Test for StatusAndBlocked trait
 */
class StatusAndBlockedTest extends \PHPUnit\Framework\TestCase {

    const DATA = [
        'i_test' => 1,
        'bill_status' => 'O',
        'blocked' => 'N'
    ];

    public function testAll() {
        $t = new Wrappers\StatusAndBlockedWrapper(self::DATA, new \Porta\Objects\Defs\DefBase('test', 'Test'));
        $this->assertEquals('O', $t->getBillStatus());
        $this->assertEquals('ok', $t->getStatus());
        $t->status = 'other';
        $this->assertEquals('other', $t->getStatus());
        $this->assertFalse($t->isBlocked());
        $t->block();
        $this->assertTrue($t->isBlocked());
        $t->unblock();
        $this->assertFalse($t->isBlocked());
    }

}
