<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Customer\Service\V1\Dto\Search;

use Magento\Customer\Service\V1\Dto\Filter;

/**
 * Groups two or more filters together using a logical group type
 */
interface FilterGroupInterface
{
    /**
     * Returns a list of filters in this group
     *
     * @return Filter[]
     */
    public function getFilters();

    /**
     * Returns a list of filter groups in this group
     *
     * @return FilterGroupInterface[]
     */
    public function getGroups();

    /**
     * Returns the grouping type such as 'OR' or 'AND'.
     *
     * @return string
     */
    public function getGroupType();
}
