<?php

/*
 * PortaOne Billing objects helper library
 * API docs: https://docs.portaone.com
 * (c) Alexey Pavlyuts <alexey@pavlyuts.ru>
 */

namespace Porta\Objects;

use Porta\Objects\Exception\PortaObjectsException;

/**
 * Extended subscription class to handle subscription which isalready attached
 *
 */
class SubscriptionUsed extends Subscription
{

    const FIELD_STATUS = 'int_status';
    const STATUS_ACTIVE = 1;
    const STATUS_PENDING = 0;
    const STATUS_CLOSED = 2;

    protected $activeLoaded = false;

    public function isActiveDataLoaded(): bool
    {
        return $this->activeLoaded;
    }

    public function updateWithActiveData(array $data): self
    {
        $this->activeLoaded = true;
        $this->data = array_merge($this->data, $data);
        return $this;
    }

    public function getStatus(): int
    {
        $this->requireActiveData();
        return $this[self::FIELD_STATUS];
    }

    public function getPaidTo(): ?\PortaDateTime
    {
        $this->requireActiveData();
        if (isset($this['billed_to'])) {
            return \PortaDateTime::fromPortaDateString($this['billed_to'], PortaFactory::$defaultTimezone)->lastMoment();
        } else {
            return null;
        }
    }

    public function getEffectiveFee(): float
    {
        return $this['effective_fee'] ?? 0;
    }

    protected function requireActiveData(): self
    {
        if (!$this->activeLoaded) {
            throw new Exception\PortaObjectsException("Tried to access active subscription data before it was updated with active data");
        }
        return $this;
    }

    protected function isFieldAllowedToWrite($offset): bool
    {
        return false;
    }
}
