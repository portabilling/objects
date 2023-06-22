<?php

/*
 * PortaOne Billing objects helper library
 * API docs: https://docs.portaone.com
 * (c) Alexey Pavlyuts <alexey@pavlyuts.ru>
 */

namespace Porta\Objects;

use Porta\Objects\Exception\PortaObjectsException;
use Porta\Objects\Defs\DefSubscription;

/**
 * Wrapper for Subscription
 *
 */
class Subscription extends PortaObject
{

    // Charge periods
    const PERIOD_DAY = 1;
    const PERIOD_WEEK = 2;
    const PERIOD_SEMIMONTH = 3;
    const PERIOD_MONTH = 4;
    //Activaton modes
    const ACTIVATION_START_DATE = 1;
    const ACTIVATION_FIRST_USAGE = 2;
    //Charging models
    const CHARGE_PROGRESSIVELY = 0;
    const CHARGE_AT_END = 1;
    const CHARGE_IN_ADVANCE = 2;
    //loading constants
    const LOAD_WITH_FEES = 1;
    const LOAD_WITH_DISCOUNTS = 2;
    const LOAD_CHECK_USAGE_BY_RESELLERS = 4;

    public function __construct(array $data)
    {
        parent::__construct($data, new DefSubscription());
    }

    public function getName()
    {
        return $this->getRequired('name');
    }

    public function getActivationMode()
    {
        return $this->getRequired('activation_mode');
    }

    public function getChargeModel()
    {
        return $this->getRequired('charge_model');
    }

    public function isDailyCharged()
    {
        return 'Y' == $this->getRequired('generate_daily_charge');
    }

    public function getFee(int $period, int $periods = 0): float
    {
        foreach ($this->getRequired('periodic_fees') as $recordset) {
            foreach ($recordset as $record) {
                if (($record['i_billing_period'] == $period) && ($record['periods']
                        == $periods)) {
                    return $record['fee'];
                }
            }
        }
        throw new PortaObjectsException("Fee not found for period #$period and periods $periods for subscription id "
                        . ($this['i_subscription'] ?? 'unknown'));
    }
}
