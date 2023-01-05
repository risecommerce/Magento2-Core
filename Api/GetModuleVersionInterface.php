<?php
/**
 * Copyright © Risecommerce (support@risecommerce.com). All rights reserved.
 * 
 */

namespace Risecommerce\Core\Api;

/**
 * Return module version by module name
 *
 * @api
 * @since 2.1.0
 */
interface GetModuleVersionInterface
{
    /**
     * Get module version
     *
     * @api
     * @param string $moduleName
     * @return string
     */
    public function execute(string $moduleName) : string;
}
