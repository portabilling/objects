<?php

/*
 * PortaOne Billing objects helper library
 * API docs: https://docs.portaone.com
 * (c) Alexey Pavlyuts <alexey@pavlyuts.ru>
 */

namespace Porta\Objects\Defs;

use Porta\Objects\Subscription;

/**
 * Definition for Subscription
 */
class DefSubscription extends DefBase
{

    const OPTION_FIELDS = [
        Subscription::LOAD_WITH_FEES => 'with_fees',
        Subscription::LOAD_WITH_DISCOUNTS => 'with_discounts',
        Subscription::LOAD_CHECK_USAGE_BY_RESELLERS => 'check_usage_by_resellers',
    ];

    public function __construct(bool $readOnly = false)
    {
        parent::__construct('subscription', 'Subscription', Subscription::class, $readOnly);
    }

    // Non-regular field name used
    public function getListFieldName(): string
    {
        return 'subscriptions';
    }
}
