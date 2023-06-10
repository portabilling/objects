<?php

/*
 * PortaOne Billing objects helper library
 * API docs: https://docs.portaone.com
 * (c) Alexey Pavlyuts <alexey@pavlyuts.ru>
 */

namespace PortaObjectsTest;

use Porta\Objects\PortaFactory;
use Porta\Objects\Addon;
use Porta\Objects\Defs\DefAddon;
use Porta\Objects\SubscriptionUsed;
use Porta\Billing\Billing;

/**
 * Class to test Addon object
 *
 */
class AddonTest extends DataTestCase {

    public function testCreate() {
        PortaFactory::setup($this->createMock(Billing::class), 'UTC');
        $obj = new Addon($this->load('addon'));
        $this->assertEquals(86, $obj->getIndex());
        $this->assertTrue($obj->hasSubscription());
        $this->assertInstanceOf(SubscriptionUsed::class, $obj->getSubscription());
    }

    public function testSetGetEffective() {
        PortaFactory::setup($this->createMock(Billing::class), 'UTC');
        $obj = new Addon($this->load('addon'));
        $this->assertEquals(\PortaDateTime::fromPortaString("2023-05-15 09:34:58", 'UTC'), $obj->getEffectiveFrom());
        $this->assertNull($obj->getEffectiveTo());
        $to = new \PortaDateTime();
        $this->assertFalse($obj->isUpdated());
        $obj->setEffectiveTo($to);
        $this->assertTrue($obj->isUpdated());
        $this->assertEquals($to->formatPorta(), $obj->getEffectiveTo()->formatPorta());
    }

    public function testIsActiveAt() {
        PortaFactory::setup($this->createMock(Billing::class), 'UTC');
        $now = new \DateTimeImmutable();
        // no effective from
        $obj = new Addon(['i_product' => 7]);
        $this->assertFalse($obj->isActiveAt($now));
        $obj->setEffectiveTo($now->modify('+1 day'));
        $this->assertFalse($obj->isActiveAt($now));

        // Eff from, No effective to
        $obj->setEffectiveTo();
        $obj->setEffectiveFrom($now->modify('-1 day'));

        $this->assertFalse($obj->isActiveAt($now->modify('-2 day')));
        $this->assertTrue($obj->isActiveAt($now));

        //Eff from, eff to
        $obj->setEffectiveTo($now->modify('+1 day'));
        $this->assertTrue($obj->isActiveAt($now));
        $this->assertFalse($obj->isActiveAt($now->modify('+2 day')));
    }

    public function testSaved() {
        $now = \PortaDateTime::fromPortaString((new \PortaDateTime())->formatPorta());
        $obj = new Addon(['i_product' => 7]);
        $this->assertFalse($obj->isUpdated());
        $obj->setEffectiveFrom($now);
        $this->assertTrue($obj->isUpdated());
        $obj->saved();
        $this->assertEquals($now, $obj->getEffectiveFrom());
    }

    public function testSubscriptionRelated() {
        $obj = new Addon($this->load('addon'));
        $obj->updateSubscriptionWithActiveData($this->load('subscription_state_86'));
        $this->assertEquals('2023-05-27 23:59:59', $obj->getPaidTo()->formatPorta());
    }

}
