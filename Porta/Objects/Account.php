<?php

/*
 * PortaOne Billing objects helper library
 * API docs: https://docs.portaone.com
 * (c) Alexey Pavlyuts <alexey@pavlyuts.ru>
 */

namespace Porta\Objects;

use Porta\Objects\Customer;
use Porta\Objects\Exception\PortaObjectsException;
use Porta\Objects\Addon;

/**
 * Wrapper class for Account
 */
class Account extends PortaObject
{

    use \Porta\Objects\Traits\StatusAndBlocked;

    // Billing model
    const FIELD_BILL_MODEL = 'billing_model';
    const BILL_MODEL_DEBIT = -1;
    const BILL_MODEL_VOUCHER = 0;
    const BILL_MODEL_CREDIT = 1;
    const BILL_MODEL_ALIAS = 2;
    const BILL_MODEL_INTERNAL = 3;
    const BILL_MODEL_BENEFICIARY = 4;
    // Billing status
    const BILL_STATUS_OPEN = 'O';
    const BILL_STATUS_INACTIVE = 'I';
    const BILL_STATUS_TERMINATED = 'C';
    // Account status
    const STATUS_OK = 'ok';
    const STATUS_CUSTOMER_EXPORTED = 'customer_exported';
    const STATUS_EXPIRED = 'expired';
    const STATUS_QUARANTINE = 'quarantine';
    const STATUS_SCREENING = 'screening';
    const STATUS_CLOSED = 'closed';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_BLOCKED = 'blocked';
    const STATUS_NOT_YET_ACTIVE = 'not_yet_active';
    const STATUS_OVERDRAFT = 'overdraft';
    const STATUS_CREDIT_EXCEEDED = 'credit_exceeded';
    const STATUS_ZERO_BALANCE = 'zero_balance';
    const STATUS_FROZEN = 'frozen';
    const STATUS_CUSTOMER_SUSPENDED = 'customer_suspended';
    const STATUS_CUSTOMER_LIMITED = 'customer_limited';
    const STATUS_CUSTOMER_PROVISIONALLY_TERMINATED = 'customer_provisionally_terminated';
    const STATUS_CUSTOMER_BLOCKED = 'customer_blocked';
    const STATUS_CUSTOMER_HAS_NO_AVAILABLE_FUNDS = 'customer_has_no_available_funds';
    const STATUS_CUSTOMER_CREDIT_EXCEED = 'customer_credit_exceed';
    const STATUS_CUSTOMER_SUSPENSION_DELAYED = 'customer_suspension_delayed';
    const STATUS_CUSTOMER_LIMITING_DELAYED = 'customer_limiting_delayed';
    // Account roles
    const FIELD_ROLE = 'i_account_role';
    const ROLE_UNIVERSAL = 1;
    const ROLE_PHONE_LINE = 2;
    const ROLE_AUTO_ATTENDANT = 3;
    const ROLE_PREPAID_CARD = 4;
    const ROLE_PINLESS = 5;
    const ROLE_IP4_ADDRESS = 6;
    const ROLE_USER_DOMAIN = 7;
    const ROLE_MOBILE = 8;
    const ROLE_VOUCHER = 9;
    // Account load control
    const LOAD_DETAILED_INFO = 1;
    const LOAD_EXPAND_ALIAS = 2;
    const LOAD_GET_INCLUDED_SERVICES = 4;
    const LOAD_GET_SERVICE_FEATURES = 8;
    const LOAD_WITH_CALL_PROCESSING_MODE = 16;
    const LOAD_WITH_CUSTOMER_INFO = 32;
    const LOAD_WITH_RESELLER_INFO = 64;
    const LOAD_WITHOUT_SERVICE_FEATURES = 128;
    protected const FIELD_ASSIGNED_ADDONS = 'assigned_addons';

    protected ?Customer $customer = null;

    /** @property Addon[] $activeAddons */
    protected array $activeAddons = [];

    /** @property Addon[] $availableAddons */
    protected ?array $availableAddons = null;

    public function __construct(array $data)
    {
        parent::__construct($data, new Defs\DefAccount());
    }

    public function setData(array $data): Account
    {
        parent::setData($data);
        $this->activeAddons = PortaFactory::createObjectsFromArray($this['assigned_addons'] ?? [], new Defs\DefAddon());
        unset($this->data['assigned_addons']);
        return $this;
    }

    public function getData(): array
    {
        $data = parent::getData();
        $data[self::FIELD_ASSIGNED_ADDONS] = [];
        foreach ($this->activeAddons as $addon) {
            $data[self::FIELD_ASSIGNED_ADDONS][] = $addon->getData();
        }
        return $data;
    }

    public function isUpdated(): bool
    {
        return parent::isUpdated() || $this->isAddonsChanged();
    }

    public function getUpdateData(): array
    {
        return array_merge(parent::getUpdateData(), $this->getAddonsUpdateData());
    }

    public function getId(): string
    {
        return $this->getRequired('id');
    }

    public function getIdNoRealm(): string
    {
        return explode('@', $this->getId())[0];
    }

    public function getBillingModel(): int
    {
        return $this->getRequired(self::FIELD_BILL_MODEL);
    }

    public function getRole(): int
    {
        return $this->getRequired(self::FIELD_ROLE);
    }

    public function getCustomerIndex(): int
    {
        return $this->getRequired('i_customer');
    }

    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    public function setCustomer(Customer $customer): self
    {
        if ($customer->getIndex() == $this->getRequired('i_customer')) {
            $this->customer = $customer;
            $customer->attachAccount($this);
        } else {
            throw new PortaObjectsException("Error attaching customer #" . $customer->getIndex()
                            . " to account #" . $this->getIndex());
        }
        return $this;
    }

    public function loadCustomer(int $options = 0, array $params = []): self
    {
        $this->setCustomer($this->loadParent(new Defs\DefCustomer(), $options, $params));
        return $this;
    }

    public function getActiveAddon(int $addonIndex): ?Addon
    {
        return $this->activeAddons[$addonIndex] ?? null;
    }

    public function getActiveAddonsIndices(): array
    {
        return array_keys($this->activeAddons);
    }

    /** @return Addon[] */
    public function getActiveAddons(): array
    {
        return $this->activeAddons;
    }

    public function addonAdd(int $addonIndex, ?\DateTimeInterface $effectiveFrom
            = null,
            ?\DateTimeInterface $effectiveTo = null): self
    {

        if (array_key_exists($addonIndex, $this->activeAddons)) {
            throw new PortaObjectsException("Trying to add add-on to account which is already exist");
        }
        if (isset($this->availableAddons[$addonIndex])) {
            $newAddon = $this->availableAddons[$addonIndex];
        } else {
            $newAddon = new Addon(['i_product' => $addonIndex]);
        }
        $newAddon->setEffectiveFrom($effectiveFrom);
        $newAddon->setEffectiveTo($effectiveTo);
        $this->activeAddons[$addonIndex] = $newAddon;
        unset($this->availableAddons[$addonIndex]);
        return $this;
    }

    public function setAddonEffectiveTo(int $addonIndex, ?\DateTimeInterface $to): self
    {
        (isset($this->activeAddons[$addonIndex])) ? $this->getActiveAddon($addonIndex)->setEffectiveTo($to)
                            : null;
        return $this;
    }

    public function addonRemove(int $addonIndex): self
    {
        unset($this->activeAddons[$addonIndex]);
        $this->updatedData[Account::FIELD_ASSIGNED_ADDONS] = true;
        return $this;
    }

    public function addonsRemove(array $addonIndices): self
    {
        foreach ($addonIndices as $index) {
            $this->addonRemove($index);
        }
        return $this;
    }

    public function getAvailableAddonsIndices(): array
    {
        return array_keys($this->getAvailableAddons());
    }

    /** @return Addon[] */
    public function getAvailableAddons(): array
    {
        return $this->availableAddons ?? $this->doLoadAvailableAddons(Addon::LOAD_DETAILED_INFO
                        + Addon::LOAD_WITH_SUBSCRIPTION);
    }

    public function loadAvailableAddons(int $options = 3): self
    {
        $this->doLoadAvailableAddons($options);
        return $this;
    }

    /** @return Addon[] */
    protected function doLoadAvailableAddons(int $options = 3): array
    {
        $addons = PortaFactory::loadList(new Defs\DefAddon(), ['i_product' => $this['i_product']], $options);
        foreach ($this->getActiveAddonsIndices() as $index) {
            unset($addons[$index]);
        }
        $this->availableAddons = $addons;
        return $this->availableAddons;
    }

    public function loadActiveSubscriptions(): self
    {
        $result = PortaFactory::$billing->call('/Account/get_subscriptions',
                [
                    'i_account' => $this->getIndex(),
                    'with_effective_fees' => 1,
                    'with_promotional_periods_info' => 1,
                    'with_regular_discount_list' => 1,
                    'with_upcharge_list' => 1
                ]
        );
        $this->attachActiveSubscriptions($result['subscriptions'] ?? []);
        return $this;
    }

    public function attachActiveSubscriptions(array $subscriptionsList): self
    {
        $byProduct = [];
        foreach ($subscriptionsList ?? [] as $record) {
            if ($record[SubscriptionUsed::FIELD_STATUS] != SubscriptionUsed::STATUS_CLOSED) {
                $byProduct[$record['i_product']][] = $record;
            }
        }
        foreach ($byProduct as $key => $data) {
            if (count($data) != 1) {
                throw new PortaObjectsException("Found multiple non-closed subscriptions for addon");
            }
            $this->activeAddons[$key]->updateSubscriptionWithActiveData($data[0]);
        }
        return $this;
    }

    public function activateSubscriptions(): self
    {
        $result = PortaFactory::$billing->call('/Account/activate_subscriptions', ['i_account' => $this->getIndex()]);
        if (1 != ($result['success'] ?? 0)) {
            throw new PortaObjectsException("Falure to force activate subcriptions for account #" . $this->getIndex());
        }
        return $this;
    }

    public function chargeSubscriptions(?int $advancePeriods = null): self
    {
        $result = PortaFactory::$billing->call('/Account/charge_subscription_fees',
                array_merge(
                        ['i_account' => $this->getIndex()],
                        is_null($advancePeriods) ? [] : ['immediately_in_advance' => $advancePeriods]
                )
        );
        if (1 != ($result['success'] ?? 0)) {
            throw new PortaObjectsException("Falure to forece charge subcriptions for account #" . $this->getIndex());
        }
        return $this;
    }

    protected function isAddonsChanged(): bool
    {
        if (key_exists(Account::FIELD_ASSIGNED_ADDONS, $this->updatedData)) {
            return true;
        }
        foreach ($this->activeAddons as $addon) {
            if ($addon->isUpdated()) {
                return true;
            }
        }
        return false;
    }

    protected function getAddonsUpdateData(): array
    {
        if (!$this->isAddonsChanged()) {
            return [];
        }
        $result = [];
        foreach ($this->activeAddons as $addon) {
            $result[] = $addon->getUpdateData();
        }
        return [self::FIELD_ASSIGNED_ADDONS => $result];
    }
}
