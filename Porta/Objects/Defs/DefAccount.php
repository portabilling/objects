<?php

/*
 * PortaOne Billing objects helper library
 * API docs: https://docs.portaone.com
 * (c) Alexey Pavlyuts <alexey@pavlyuts.ru>
 */

namespace Porta\Objects\Defs;

use Porta\Objects\Account;

/**
 * Defenition for Account
 *
 */
class DefAccount extends DefBase
{

    const OPTION_FIELDS = [
        Account::LOAD_DETAILED_INFO => 'detailed_info',
        Account::LOAD_EXPAND_ALIAS => 'expand_alias',
        Account::LOAD_GET_INCLUDED_SERVICES => 'get_included_services',
        Account::LOAD_GET_SERVICE_FEATURES => 'get_service_features',
        Account::LOAD_WITH_CALL_PROCESSING_MODE => 'with_call_processing_mode',
        Account::LOAD_WITH_CUSTOMER_INFO => 'with_customer_info',
        Account::LOAD_WITH_RESELLER_INFO => 'with_reseller_info',
        Account::LOAD_WITHOUT_SERVICE_FEATURES => 'without_service_features',
    ];

    public function __construct()
    {
        parent::__construct('account', 'Account', \Porta\Objects\Account::class);
    }
}
