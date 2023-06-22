<?php

/*
 * PortaOne Billing objects helper library
 * API docs: https://docs.portaone.com
 * (c) Alexey Pavlyuts <alexey@pavlyuts.ru>
 */

namespace Porta\Objects;

use Porta\Objects\Account;
use Porta\Objects\Exception\PortaObjectsException;

/**
 * Wrapper class for Customer
 *
 */
class Customer extends PortaObject
{

    use \Porta\Objects\Traits\StatusAndBlocked;

// Balance control
    const FIELD_BALANCE_CONTROL_TYPE = 'i_balance_control_type';
    const POSTPAID = 1;
    const PREPAID = 2;
// Customer billing status
    const BILL_STATUS_OPEN = 'O';
    const BILL_STATUS_SUSPENDED = 'S';
    const BILL_STATUS_CLOSED = 'C';
    const BILL_STATUS_EXPORTED = 'E';
    const BILL_STATUS_DEACTIVATED = 'D';
// Customer status
    const STATUS_OK = 'ok'; // Means there no 'status' field in the API dataset
    const STATUS_CLOSED = 'closed';
    const STATUS_EXPORTED = 'exported';
    const STATUS_BLOCKED = 'blocked';
    const STATUS_SUSPENDED = 'suspended';
    const STATUS_PROVISIONALLY_TERMINATED = 'provisionally_terminated';
    const STATUS_LIMITED = 'limited';
    const STATUS_BILLING_PAUSED = 'billing_paused';
    const STATUS_CREDIT_EXCEEDED = 'credit_exceeded';
    const STATUS_NO_AVAILABLE_FUNDS = 'no_available_funds';
    const STATUS_SUSPENSION_DELAYED = 'suspension_delayed';
    const STATUS_LIMITING_DELAYED = 'limiting_delayed';
    const STATUS_FROZEN = 'frozen';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_SPENDING_LIMIT_REACHED = 'spending_limit_reached';
    const STATUS_RESELLER_BLOCKED = 'reseller_blocked';
    const STATUS_RESELLER_SUSPENDED = 'reseller_suspended';
    const STATUS_RESELLER_PROVISIONALLY_TERMINATED = 'reseller_provisionally_terminated';
// Customer load control
    const LOAD_DETAILED_INFO = 1;
    const LOAD_EFFECTIVE_VALUES = 2;
    const LOAD_TIME_ZONE_NAME = 4;
    const LOAD_ALL = 7;

    /** @propery Account[] $accounts */
    protected $accounts = null;

    public function __construct(array $data)
    {
        parent::__construct($data, new Defs\DefCustomer());
    }

    public function getName(): string
    {
        return $this->getRequired('name');
    }

    public function isPrepaid(): bool
    {
        return self::PREPAID == $this->getRequired(self::FIELD_BALANCE_CONTROL_TYPE);
    }

    public function isCreditLimitSet(): bool
    {
        return isset($this['credit_limit']);
    }

    public function isAvailableFundsUnlimited(): bool
    {
        return !$this->isPrepaid() && !$this->isCreditLimitSet();
    }

    public function getAvailableFunds(): ?float
    {
        return $this->isCreditLimitSet() ? $this['credit_limit'] - $this['balance']
                    : null;
    }

    /** @return Account[] */
    public function getAccounts(): array
    {
        return $this->accounts ?? $this->doLoadAccounts(Account::LOAD_DETAILED_INFO);
    }

    public function getAccount(int $accountIndex): Account
    {
        return $this->getAccounts()[$accountIndex] ?? null;
    }

    public function getAccountsIndices(): array
    {
        return array_keys($this->getAccounts());
    }

    public function loadAccounts(int $options = 0, array $params = []): self
    {
        $this->doLoadAccounts($options, $params);
        return $this;
    }

    /** @return Account[] */
    protected function doLoadAccounts(int $options = 0, array $params = []): array
    {
        $this->accounts = $this->loadChildren(new Defs\DefAccount(), $options, $params);
        foreach ($this->accounts as $account) {
            $this->attachAccount($account);
        }
        return $this->accounts;
    }

    public function attachAccount(Account $account): self
    {
        if ($account->getRequired('i_customer') == $this->getIndex()) {
            $this->accounts[$account->getIndex()] = $account;
            if ($account->getCustomer() !== $this) {
                $account->setCustomer($this);
            }
        } else {
            throw new PortaObjectsException("Error attaching account #" . $account->getIndex()
                            . " to customer #" . $this->getIndex());
        }
        return $this;
    }
}
