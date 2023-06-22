<?php

/*
 * PortaOne Billing objects helper library
 * API docs: https://docs.portaone.com
 * (c) Alexey Pavlyuts <alexey@pavlyuts.ru>
 */

namespace Porta\Objects\Traits;

/**
 * Trait for shared Customer and Account status handling
 */
trait StatusAndBlocked
{

    public function getBillStatus(): string
    {
        return $this->getRequired('bill_status');
    }

    public function getStatus(): string
    {
        return $this['status'] ?? static::STATUS_OK;
    }

    public function isBlocked(): bool
    {
        return 'Y' == $this->getRequired('blocked');
    }

    public function block(): void
    {
        $this['blocked'] = 'Y';
    }

    public function unblock(): void
    {
        $this['blocked'] = 'N';
    }
}
