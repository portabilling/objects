<?php

/*
 * PortaOne Billing objects helper library
 * API docs: https://docs.portaone.com
 * (c) Alexey Pavlyuts <alexey@pavlyuts.ru>
 */

namespace Porta\Objects\Defs;

use Porta\Objects\Customer;

/**
 * Definition class for Customer
 */
class DefCustomer extends DefBase {

    const OPTION_FIELDS = [
        Customer::LOAD_DETAILED_INFO => 'detailed_info',
        Customer::LOAD_EFFECTIVE_VALUES => 'effective_values',
        Customer::LOAD_TIME_ZONE_NAME => 'get_time_zone_name',
    ];

    public function __construct() {
        parent::__construct('customer', 'Customer', \Porta\Objects\Customer::class);
    }

}
