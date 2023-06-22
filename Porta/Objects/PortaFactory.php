<?php

/*
 * PortaOne Billing objects helper library
 * API docs: https://docs.portaone.com
 * (c) Alexey Pavlyuts <alexey@pavlyuts.ru>
 */

namespace Porta\Objects;

use Porta\Billing\Billing;
use Porta\Billing\BulkOperation;
use Porta\Objects\Defs\DefInterface;

/**
 * Factory class for billing objects
 *
 * @author alexe
 */
class PortaFactory
{

    static Billing $billing;
    static string $defaultTimezone;

    /**
     * Setup object factory for billing and defailt timezone
     *
     * @param Billing $billing
     * @param string|null $defaultTimezone
     */
    public static function setup(Billing $billing, string $defaultTimezone = 'UTC')
    {
        static::$billing = $billing;
        static::$defaultTimezone = $defaultTimezone;
    }

    /* static methods to load objects from from biling */

    public static function createObjectFromData(array $data, DefInterface $def)
    {
        $className = $def->getClass();
        return new $className($data, $def);
    }

    public static function createObjectsFromArray(array $list, DefInterface $def): array
    {
        $result = [];
        foreach ($list as $data) {
            $entity = static::createObjectFromData($data, $def);
            $result[$entity->getIndex()] = $entity;
        }
        return $result;
    }

    public static function loadByIndex(int $index, DefInterface $def, array $params
            = [], int $options = 0)
    {
        $answer = PortaFactory::$billing->call(
                $def->getLoadMethod(),
                array_merge([$def->getIndexField() => $index], $def->buildLoadOptions($options), $params)
        );
        return static::makeFromAnswer($answer, $def);
    }

    public static function loadConcurrentByIndex(array $indices, DefInterface $def, array $params
            = [], int $options = 0, int $concurency = 20)
    {
        $operations = [];
        $readyParams = array_merge($def->buildLoadOptions($options), $params);
        $endpoint = $def->getLoadMethod();
        $indexField = $def->getIndexField();
        foreach ($indices as $index) {
            $operations[] = new BulkOperation($endpoint, array_merge([$indexField => $index], $readyParams));
        }
        PortaFactory::$billing->callConcurrent($operations, $concurency);
        $result = [];
        $resultField = $def->getLoadFieldName();
        foreach ($operations as $operation) {
            if ($operation->success()) {
                $data = $operation->getResponse();
                if (isset($data[$resultField])) {
                    $obj = static::createObjectFromData($data[$resultField], $def);
                    $result[$obj->getIndex()] = $obj;
                }
            }
        }
        return $result;
    }

    public static function loadConcurrentSupplamentaryByIndex(array $indices, string $method,
            string $keyField, string $answerField,
            array $params = [], int $concurrency = 20): array
    {
        $operations = [];
        foreach ($indices as $index) {
            $operations[$index] = new BulkOperation($method, array_merge([$keyField => $index], $params));
        }
        PortaFactory::$billing->callConcurrent($operations, $concurency);
        $result = [];
        foreach ($operations as $key => $operation) {
            $data = $operation->getResponse();
            if (isset($data[$answerField])) {
                $result[] = $data[$answerField];
            }
        }
        return $result;
    }

    public static function loadList(DefInterface $def, array $params = [], int $options
            = 0): ?array
    {
        $answer = PortaFactory::$billing->call($def->getListMethod(), array_merge($def->buildLoadOptions($options), $params));
        $element = $def->getListFieldName();
        if (!isset($answer[$element])) {
            return null;
        }
        return static::createObjectsFromArray($answer[$element], $def);
    }

    public static function loadListGeneratorBulk(DefInterface $def, int $limit, array $params
            = [], int $options = 0)
    {
        $params = array_merge($def->buildLoadOptions($options), $params, ['offset' => 0, 'limit' => $limit]);
        do {
            $result = self::loadList($def, $params, $options);
            if (is_null($result)) {
                break;
            }
            yield $result;
            $params['offset'] += $limit;
        } while (count($result) == $limit);
    }

    public static function loadListGeneratorByOne(DefInterface $def, int $limit, array $params
            = [], int $options = 0)
    {
        foreach (self::loadListGeneratorBulk($def, $limit, $params, $options) as $group) {
            foreach ($group as $element) {
                yield $element;
            }
        }
    }

    public static function loadAccountByIndex(int $index, int $options = 0, array $params
            = []): ?Account
    {
        return static::loadAccount(array_merge(['i_account' => $index], $params), $options);
    }

    public static function loadAccountById(string $id, int $options = 0, array $params
            = []): ?Account
    {
        return static::loadAccount(array_merge(['id' => $id], $params), $options);
    }

    protected static function loadAccount(array $params, int $options = 0): ?Account
    {
        $defAccount = new Defs\DefAccount();
        $answer = PortaFactory::$billing->call(
                $defAccount->getLoadMethod(),
                array_merge($defAccount->buildLoadOptions($options), $params)
        );
        if (null === ($account = static::makeFromAnswer($answer, $defAccount))) {
            return null;
        }
        if (($customer = static::makeFromAnswer($answer, new Defs\DefCustomer())) instanceof Customer) {
            $account->setCustomer($customer);
        }
        return $account;
    }

    protected static function makeFromAnswer(array $answer, DefInterface $def)
    {
        $element = $def->getLoadFieldName();
        if (!isset($answer[$element]) || ([] == $answer[$element])) {
            return null;
        }
        return static::createObjectFromData($answer[$element], $def);
    }
}
