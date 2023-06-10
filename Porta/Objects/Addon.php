<?php

/*
 * PortaOne Billing objects helper library
 * API docs: https://docs.portaone.com
 * (c) Alexey Pavlyuts <alexey@pavlyuts.ru>
 */

namespace Porta\Objects;

/**
 * Wrapper for Addon product
 *
 */
class Addon extends PortaObject {

    // Load options
    const LOAD_DETAILED_INFO = 1;
    const LOAD_WITH_SUBSCRIPTION = 2;
    // Fields
    const FIELD_EFFECTIVE_FROM = 'addon_effective_from';
    const FIELD_EFFECTIVE_TO = 'addon_effective_to';

    protected ?SubscriptionUsed $subscription = null;

    public function __construct(array $data) {
        parent::__construct($data, new Defs\DefAddon());
    }

    public function setData(array $data): self {
        parent::setData($data);
        if (isset($this['product_subscription'])) {
            $this->subscription = new SubscriptionUsed($this['product_subscription']);
        }
        return $this;
    }

    public function hasSubscription(): bool {
        return isset($this->subscription);
    }

    public function getSubscription(): ?SubscriptionUsed {
        return $this->subscription;
    }

    public function updateSubscriptionWithActiveData(array $data): self {
        if (!is_null($this->subscription)) {
            $this->subscription->updateWithActiveData($data);
        }
        return $this;
    }

    public function getPaidTo(): ?\PortaDateTime {
        return is_null($this->subscription) ? null : $this->subscription->getPaidTo();
    }

    public function setEffectiveFrom(?\DateTimeInterface $from): self {
        $this[self::FIELD_EFFECTIVE_FROM] = is_null($from) ? null : \PortaDateTime::formatDateTime($from);
        return $this;
    }

    public function getEffectiveFrom(): ?\PortaDateTime {
        if (isset($this[self::FIELD_EFFECTIVE_FROM])) {
            return \PortaDateTime::fromPortaString($this[self::FIELD_EFFECTIVE_FROM], PortaFactory::$defaultTimezone);
        }
        return null;
    }

    public function getEffectiveTo(): ?\PortaDateTime {
        if (isset($this[self::FIELD_EFFECTIVE_TO])) {
            return \PortaDateTime::fromPortaString($this[self::FIELD_EFFECTIVE_TO], PortaFactory::$defaultTimezone);
        }
        return null;
    }

    public function setEffectiveTo(?\DateTimeInterface $to = null): self {
        $this[self::FIELD_EFFECTIVE_TO] = is_null($to) ? null : \PortaDateTime::formatDateTime($to);
        return $this;
    }

    public function getUpdateData(): array {
        return [
            $this->def->getIndexField() => $this->index,
            Addon::FIELD_EFFECTIVE_FROM =>
            (isset($this[Addon::FIELD_EFFECTIVE_FROM])) //
            ? $this->getEffectiveFrom()->formatPorta() //
            : (new \PortaDateTime())->formatPorta(),
            Addon::FIELD_EFFECTIVE_TO =>
            (isset($this[Addon::FIELD_EFFECTIVE_TO])) //
            ? $this->getEffectiveTo()->formatPorta() //
            : null,
        ];
    }

    public function isActiveAt(\DateTimeInterface $moment): bool {
        $from = $this->getEffectiveFrom();
        $to = $this->getEffectiveTo();
        return !is_null($from) && ($moment >= $from) && (is_null($to) || ($moment <= $to));
    }

    public function saved(): self {
        $this->setData($this->getData());
        return $this;
    }

    protected function isFieldAllowedToWrite($offset): bool {
        return in_array($offset, [Addon::FIELD_EFFECTIVE_FROM, Addon::FIELD_EFFECTIVE_TO]);
    }

}
