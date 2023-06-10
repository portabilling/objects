<?php

/*
 * PortaOne Billing objects helper library
 * API docs: https://docs.portaone.com
 * (c) Alexey Pavlyuts <alexey@pavlyuts.ru>
 */

namespace Porta\Objects\Defs;

/**
 * Base class for Portaone object definition
 */
class DefBase implements DefInterface {

    protected $key;
    protected $base;
    protected $handlingClass;

    const OPTION_FIELDS = [];

    public function __construct(string $key, string $base,
            ?string $handlingClass = \Porta\Objects\PortaObject::class) {
        $this->key = $key;
        $this->base = $base;
        $this->handlingClass = $handlingClass ?? \Porta\Objects\PortaObject::class;
    }

    public function getKey(): string {
        return $this->key;
    }

    public function getClass(): string {
        return $this->handlingClass;
    }

    public function getIndexField(): string {
        return 'i_' . $this->key;
    }

    public function getLoadMethod(): string {
        return $this->buildMethod('get_', '_info');
    }

    public function getLoadFieldName(): string {
        return $this->key . '_info';
    }

    public function getListMethod(): string {
        return $this->buildMethod('get_', '_list');
    }

    public function getListFieldName(): string {
        return $this->key . '_list';
    }

    public function getCreateMethod(): ?string {
        return $this->buildMethod('add_');
    }

    public function getCreateFieldName(): ?string {
        return $this->key . '_info';
    }

    public function getUpdateMethod(): ?string {
        return $this->buildMethod('update_');
    }

    public function getUpdateFieldName(): ?string {
        return $this->key . '_info';
    }

    public function buildLoadOptions(int $options): array {
        $optionsArray = [];
        foreach (static::OPTION_FIELDS as $key => $field) {
            if (($key & $options) > 0) {
                $optionsArray[$field] = 1;
            }
        }
        return $optionsArray;
    }

    protected function buildMethod(string $prefix, string $postfix = '') {
        return '/' . $this->base . '/' . $prefix . $this->key . $postfix;
    }

}
