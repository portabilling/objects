<?php

/*
 * PortaOne Billing objects helper library
 * API docs: https://docs.portaone.com
 * (c) Alexey Pavlyuts <alexey@pavlyuts.ru>
 */

namespace Porta\Objects;

use Porta\Objects\Defs\DefInterface;
use Porta\Objects\Exception\PortaObjectsException;

/**
 * Basic class for Portaone objects.
 *
 * Theoretically, will work with any object with unique ID forrmed as i_xxxxxx,
 * and may be described as DefInterface object.
 *
 */
class PortaObject implements \ArrayAccess {

    protected DefInterface $def;
    protected ?int $index;
    protected array $data;
    protected array $updatedData = [];

    public function __construct(array $data, DefInterface $def) {
        $this->def = $def;
        $this->setData($data);
    }

    public function getDef(): DefInterface {
        return $this->def;
    }

    public function isNew(): bool {
        return is_null($this->index);
    }

    public function getIndex(): ?int {
        return $this->index;
    }

    public function setIndex(int $index): self {
        $this->index = $index;
        $this->data[$this->def->getIndexField()] = $index;
        return $this;
    }

    public function setData(array $data): self {
        $this->data = $data;
        $this->index = $data[$this->def->getIndexField()] ?? null;
        $this->updatedData = [];
        return $this;
    }

    public function getData(): array {
        return array_merge($this->data, $this->updatedData);
    }

    public function isUpdated(): bool {
        return ([] !== $this->updatedData) && !$this->isNew();
    }

    public function getUpdateData(): array {
        return array_merge([$this->def->getIndexField() => $this->index], $this->updatedData);
    }

    public function write(): self {
        if ($this->isNew()) {
            $answer = PortaFactory::$billing->call($this->def->getCreateMethod(), [$this->def->getUpdateFieldName() => $this->getData()]);
            if (isset($answer[$this->def->getIndexField()])) {
                $this->setIndex($answer[$this->def->getIndexField()]);
            } else {
                throw new PortaObjectsException("Failed to get id of created object for class " . static::class);
            }
        } elseif ($this->isUpdated()) {
            $answer = PortaFactory::$billing->call($this->def->getUpdateMethod(),
                    [$this->def->getUpdateFieldName() => $this->getUpdateData()]);
            if (!isset($answer[$this->def->getIndexField()])) {
                throw new PortaObjectsException("Failed to get id of modified object for class " . static::class);
            }
        } else {
            return $this;
        }
        $this->setData($this->getData());
        return $this;
    }

    public function getPortaDateTime(string $offset, ?string $timezone = null): ?\PortaDateTime {
        if (!isset($this[$offset])) {
            return null;
        }
        if (is_null($timezone)) {
            $timezone = PortaFactory::$defaultTimezone;
        }
        try {
            return \PortaDateTime::fromPortaString($this[$offset], $timezone);
        } catch (\Exception $ex) {
            throw new PortaObjectsException("Can't create PortaDateTime object from field '$offset' of class " . static::class);
        }
    }

    public function getRequired($offset) {
        if (!isset($this[$offset])) {
            throw new PortaObjectsException("Required data field '$offset'not found in the class " . static::class);
        }
        return $this[$offset];
    }

    /**
     * Loads an parent object which refereced by i_xxxx field of this object.
     *
     * For example, if $this is an account, it has i_customer reference to the
     * customer who owns the account. This metod with DefCustomer defenition
     * object will return parent Customer object
     *
     * @param DefInterface $def - defenition of parent object type
     * @param int $options - options of load parent object call, if any
     * @param array $params - extra params for call to load parent object
     *
     * @return mixed - Parent object loaded or null if any problem
     */
    public function loadParent(DefInterface $def, int $options = 0, array $params = []): ?self {
        return PortaFactory::loadByIndex($this->getRequired($def->getIndexField()), $def, $params, $options);
    }

    /**
     * Load child objects which references this object by index.
     *
     * For example, if $this is a customer, it has i_customer index, referenced
     * by Acounts. This method with DefAccount definition object will return a list
     * of Account obectsor empty array.
     *
     * @param DefInterface $def - defenition of child object type
     * @param int $options - options for load child object call, if any
     * @param array $params - extra params for call to load child objects
     *
     * @return PortaObject[] child objects or [] if it is not found
     */
    public function loadChildren(DefInterface $def, int $options = 0, array $params = []): array {
        return PortaFactory::loadList($def, array_merge([$this->def->getIndexField() => $this->getIndex()], $params), $options);
    }

    /* Methods to allow Porta\Objects operate as arrays */

    public function offsetExists($offset): bool {
        return array_key_exists($offset, $this->updatedData)//
                ? !is_null($this->updatedData[$offset]) //
                : isset($this->data[$offset]);
    }

    public function offsetGet($offset) {
        return array_key_exists($offset, $this->updatedData) ? $this->updatedData[$offset] : ($this->data[$offset] ?? null);
    }

    public function offsetSet($offset, $value): void {
        $this->throwIfReadOnly($offset);
        if ($value instanceof \PortaDateTime) {
            $value = $value->formatPorta();
        }
        $this->updatedData[$offset] = $value;
    }

    public function offsetUnset($offset): void {
        $this->throwIfReadOnly($offset);
        if ($offset == $this->def->getIndexField()) {
            throw new PortaObjectsException("Violation: try to unset of object Id");
        }
        $this->updatedData[$offset] = null;
    }

    protected function throwIfReadOnly($offset): void {
        if (!$this->isFieldAllowedToWrite($offset)) {
            throw new PortaObjectsException("Trying to write to read-only object for class " . static::class);
        }
    }

    protected function isFieldAllowedToWrite($offset): bool {
        return true;
    }

    /* Methods, allowing use of data elements as class properties */

    public function __get($name) {
        return $this->offsetGet($name);
    }

    public function __isset($name): bool {
        return $this->offsetExists($name);
    }

    public function __set($name, $value): void {
        $this->offsetSet($name, $value);
    }

    public function __unset($name) {
        $this->offsetUnset($name);
    }

}
