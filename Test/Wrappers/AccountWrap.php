<?php

/*
 * PortaOne Billing objects helper library
 * API docs: https://docs.portaone.com
 * (c) Alexey Pavlyuts <alexey@pavlyuts.ru>
 */

namespace PortaObjectsTest\Wrappers;

use Porta\Objects\Account;

/**
 * Wrapper for Account
 *
 */
class AccountWrap extends Account {

    public function spyData() {
        return $this->data;
    }

    public function spyUpdatedData() {
        return $this->updatedData;
    }

}
