<?php

/*
 * PortaOne Billing objects helper library
 * API docs: https://docs.portaone.com
 * (c) Alexey Pavlyuts <alexey@pavlyuts.ru>
 */

namespace PortaObjectsTest\Wrappers;

/**
 *  StatusAndBlocked trait wrapper
 */
class StatusAndBlockedWrapper extends \Porta\Objects\PortaObject {

    use \Porta\Objects\Traits\StatusAndBlocked;

    const STATUS_OK = 'ok';

}
