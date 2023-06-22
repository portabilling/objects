<?php

/*
 * PortaOne Billing objects helper library
 * API docs: https://docs.portaone.com
 * (c) Alexey Pavlyuts <alexey@pavlyuts.ru>
 */

namespace Porta\Objects\Defs;

use Porta\Objects\Addon;

/**
 * Definition for addon product handling within Account, it's set read-only
 * except of methods to manage from-to dates. Addon may not be saved directly.
 *
 */
class DefAddon extends DefBase
{

    const OPTION_FIELDS = [
        Addon::LOAD_DETAILED_INFO => 'detailed_info',
        Addon::LOAD_WITH_SUBSCRIPTION => 'with_subscription',
    ];

    public function __construct()
    {
        parent::__construct('product', '', \Porta\Objects\Addon::class);
    }

    public function getListMethod(): string
    {
        return '/Product/get_allowed_addons';
    }
}
