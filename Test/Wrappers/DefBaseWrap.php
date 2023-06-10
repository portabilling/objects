<?php

/*
 * PortaOne Billing objects helper library
 * API docs: https://docs.portaone.com
 * (c) Alexey Pavlyuts <alexey@pavlyuts.ru>
 */

namespace PortaObjectsTest\Wrappers;

/**
 * Wrapper to test DefBase options handling feature
 */
class DefBaseWrap extends \Porta\Objects\Defs\DefBase {

    const OPTION_1 = 1;
    const OPTION_2 = 2;
    const OPTION_4 = 4;
    const OPTION_FIELDS = [
        self::OPTION_1 => 'field_option_1',
        self::OPTION_2 => 'field_option_2',
        self::OPTION_4 => 'field_option_4',
    ];

}
