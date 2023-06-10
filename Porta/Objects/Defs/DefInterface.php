<?php

/*
 * PortaOne Billing objects helper library
 * API docs: https://docs.portaone.com
 * (c) Alexey Pavlyuts <alexey@pavlyuts.ru>
 */

namespace Porta\Objects\Defs;

/**
 *  Interface for Portabilling objects definition
 */
interface DefInterface {

    public function getKey(): string;

    public function getClass(): string;

    public function getIndexField(): string;

    public function getLoadMethod(): string;

    public function getLoadFieldName(): string;

    public function getListMethod(): string;

    public function getListFieldName(): string;

    public function getCreateMethod(): ?string;

    public function getCreateFieldName(): ?string;

    public function getUpdateMethod(): ?string;

    public function getUpdateFieldName(): ?string;

    public function buildLoadOptions(int $options): array;
}
