<?php
/**
 * Store loader
 *
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
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Core\Model\Store\Storage;

use Magento\Core\Exception;
use Magento\App\State;
use Magento\Core\Model\Store;
use Magento\Core\Model\Store\StorageInterface;
use Magento\Core\Model\Store\Group;
use Magento\Core\Model\Store\Group\Factory;
use Magento\Core\Model\Store\Exception as StoreException;
use Magento\Core\Model\StoreFactory;
use Magento\Core\Model\StoreManagerInterface;
use Magento\Core\Model\Website;
use Magento\Core\Model\Website\Factory as WebsiteFactory;
use Magento\Profiler;

class Db implements StorageInterface
{
    /**
     * Requested scope code
     *
     * @var string
     */
    protected $_scopeCode;

    /**
     * Requested scope type
     *
     * @var string
     */
    protected $_scopeType;

    /**
     * Flag that shows that system has only one store view
     *
     * @var bool
     */
    protected $_hasSingleStore;

    /**
     * Flag is single store mode allowed
     *
     * @var bool
     */
    protected $_isSingleStoreAllowed = true;

    /**
     * Application store object
     *
     * @var Store
     */
    protected $_store;

    /**
     * Stores cache
     *
     * @var Store[]
     */
    protected $_stores = array();

    /**
     * Application website object
     *
     * @var Website
     */
    protected $_website;

    /**
     * Websites cache
     *
     * @var Website[]
     */
    protected $_websites = array();

    /**
     * Groups cache
     *
     * @var Group
     */
    protected $_groups = array();

    /**
     * Config model
     *
     * @var \Magento\App\ConfigInterface
     */
    protected $_config;

    /**
     * Default store code
     *
     * @var string
     */
    protected $_currentStore = null;

    /**
     * Store factory
     *
     * @var StoreFactory
     */
    protected $_storeFactory;

    /**
     * Website factory
     *
     * @var WebsiteFactory
     */
    protected $_websiteFactory;

    /**
     * Group factory
     *
     * @var Factory
     */
    protected $_groupFactory;

    /**
     * Cookie model
     *
     * @var \Magento\Stdlib\Cookie
     */
    protected $_cookie;

    /**
     * Application state model
     *
     * @var State
     */
    protected $_appState;

    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    protected $_url;

    /**
     * @var \Magento\App\Http\Context
     */
    protected $_httpContext;

    /**
     * @param StoreFactory $storeFactory
     * @param WebsiteFactory $websiteFactory
     * @param Factory $groupFactory
     * @param \Magento\App\ConfigInterface $config
     * @param \Magento\Stdlib\Cookie $cookie
     * @param State $appState
     * @param \Magento\Backend\Model\UrlInterface $url
     * @param \Magento\App\Http\Context $httpContext
     * @param $isSingleStoreAllowed
     * @param $scopeCode
     * @param $scopeType
     * @param null $currentStore
     */
    public function __construct(
        StoreFactory $storeFactory,
        WebsiteFactory $websiteFactory,
        Factory $groupFactory,
        \Magento\App\ConfigInterface $config,
        \Magento\Stdlib\Cookie $cookie,
        State $appState,
        \Magento\Backend\Model\UrlInterface $url,
        \Magento\App\Http\Context $httpContext,
        $isSingleStoreAllowed,
        $scopeCode,
        $scopeType,
        $currentStore = null
    ) {
        $this->_storeFactory = $storeFactory;
        $this->_websiteFactory = $websiteFactory;
        $this->_groupFactory = $groupFactory;
        $this->_scopeCode = $scopeCode;
        $this->_scopeType = $scopeType ?: StoreManagerInterface::SCOPE_TYPE_STORE;
        $this->_config = $config;
        $this->_isSingleStoreAllowed = $isSingleStoreAllowed;
        $this->_appState = $appState;
        $this->_cookie = $cookie;
        $this->_url = $url;
        $this->_httpContext = $httpContext;
        if ($currentStore) {
            $this->_currentStore = $currentStore;
        }
    }

    /**
     * Get default store
     *
     * @return Store
     */
    protected function _getDefaultStore()
    {
        if (empty($this->_store)) {
            $this->_store = $this->_storeFactory->create()
                ->setId(\Magento\Core\Model\Store::DISTRO_STORE_ID)
                ->setCode(\Magento\Core\Model\Store::DEFAULT_CODE);
        }
        return $this->_store;
    }

    /**
     * Initialize currently ran store
     *
     * @return void
     * @throws StoreException
     */
    public function initCurrentStore()
    {
        Profiler::start('init_stores');
        $this->_initStores();
        Profiler::stop('init_stores');

        if (empty($this->_scopeCode) && false == is_null($this->_website)) {
            $this->_scopeCode = $this->_website->getCode();
            $this->_scopeType = StoreManagerInterface::SCOPE_TYPE_WEBSITE;
        }
        switch ($this->_scopeType) {
            case StoreManagerInterface::SCOPE_TYPE_STORE:
                $this->_currentStore = $this->_scopeCode;
                break;
            case StoreManagerInterface::SCOPE_TYPE_GROUP:
                $this->_currentStore = $this->_getStoreByGroup($this->_scopeCode);
                break;
            case StoreManagerInterface::SCOPE_TYPE_WEBSITE:
                $this->_currentStore = $this->_getStoreByWebsite($this->_scopeCode);
                break;
            default:
                $this->throwStoreException();
        }

        if (!empty($this->_currentStore)) {
            $this->_checkCookieStore($this->_scopeType);
            $this->_checkGetStore($this->_scopeType);
        }
    }

    /**
     * Check get store
     *
     * @param string $type
     * @return void
     */
    protected function _checkGetStore($type)
    {
        if (empty($_GET)) {
            return;
        }

        if (!isset($_GET['___store'])) {
            return;
        }

        $store = $_GET['___store'];
        if (!isset($this->_stores[$store])) {
            return;
        }

        $storeObj = $this->_stores[$store];
        if (!$storeObj->getId() || !$storeObj->getIsActive()) {
            return;
        }

        /**
         * prevent running a store from another website or store group,
         * if website or store group was specified explicitly
         */
        $curStoreObj = $this->_stores[$this->_currentStore];
        if ($type == 'website' && $storeObj->getWebsiteId() == $curStoreObj->getWebsiteId()) {
            $this->_currentStore = $store;
        } elseif ($type == 'group' && $storeObj->getGroupId() == $curStoreObj->getGroupId()) {
            $this->_currentStore = $store;
        } elseif ($type == 'store') {
            $this->_currentStore = $store;
        }

        if ($this->_currentStore == $store) {
            $store = $this->getStore($store);
            if ($store->getWebsite()->getDefaultStore()->getId() == $store->getId()) {
                $this->_cookie->set(Store::COOKIE_NAME, null);
            } else {
                $this->_cookie->set(Store::COOKIE_NAME, $this->_currentStore, true);
                $this->_httpContext->setValue(Store::ENTITY, $this->_currentStore);
            }
        }
        return;
    }

    /**
     * Check cookie store
     *
     * @param string $type
     * @return void
     */
    protected function _checkCookieStore($type)
    {
        if (!$this->_cookie->get()) {
            return;
        }

        $store = $this->_cookie->get(Store::COOKIE_NAME);
        if ($store && isset($this->_stores[$store])
            && $this->_stores[$store]->getId()
            && $this->_stores[$store]->getIsActive()
        ) {
            if ($type == 'website'
                && $this->_stores[$store]->getWebsiteId() == $this->_stores[$this->_currentStore]->getWebsiteId()
            ) {
                $this->_currentStore = $store;
            }
            if ($type == 'group'
                && $this->_stores[$store]->getGroupId() == $this->_stores[$this->_currentStore]->getGroupId()
            ) {
                $this->_currentStore = $store;
            }
            if ($type == 'store') {
                $this->_currentStore = $store;
            }
        }
    }

    /**
     * Retrieve store code or null by store group
     *
     * @param int $group
     * @return string|null
     */
    protected function _getStoreByGroup($group)
    {
        if (!isset($this->_groups[$group])) {
            return null;
        }
        if (!$this->_groups[$group]->getDefaultStoreId()) {
            return null;
        }
        return $this->_stores[$this->_groups[$group]->getDefaultStoreId()]->getCode();
    }

    /**
     * Retrieve store code or null by website
     *
     * @param int|string $website
     * @return string|null
     */
    protected function _getStoreByWebsite($website)
    {
        if (!isset($this->_websites[$website])) {
            return null;
        }
        if (!$this->_websites[$website]->getDefaultGroupId()) {
            return null;
        }
        return $this->_getStoreByGroup($this->_websites[$website]->getDefaultGroupId());
    }

    /**
     * Init store, group and website collections
     *
     * @return void
     */
    protected function _initStores()
    {
        $this->_store    = null;
        $this->_stores   = array();
        $this->_groups   = array();
        $this->_websites = array();

        $this->_website  = null;

        /** @var $websiteCollection \Magento\Core\Model\Resource\Website\Collection */
        $websiteCollection = $this->_websiteFactory->create()->getCollection();
        $websiteCollection->setLoadDefault(true);

        /** @var $groupCollection \Magento\Core\Model\Resource\Store\Group\Collection */
        $groupCollection = $this->_groupFactory->create()->getCollection();
        $groupCollection->setLoadDefault(true);

        /** @var $storeCollection \Magento\Core\Model\Resource\Store\Collection */
        $storeCollection = $this->_storeFactory->create()->getCollection();
        $storeCollection->setLoadDefault(true);

        $this->_hasSingleStore = false;
        if ($this->_isSingleStoreAllowed) {
            $this->_hasSingleStore = $storeCollection->count() < 3;
        }

        $websiteStores = array();
        $websiteGroups = array();
        $groupStores   = array();

        foreach ($storeCollection as $store) {
            /** @var $store Store */
            $store->setWebsite($websiteCollection->getItemById($store->getWebsiteId()));
            $store->setGroup($groupCollection->getItemById($store->getGroupId()));

            $this->_stores[$store->getId()] = $store;
            $this->_stores[$store->getCode()] = $store;

            $websiteStores[$store->getWebsiteId()][$store->getId()] = $store;
            $groupStores[$store->getGroupId()][$store->getId()] = $store;

            if (is_null($this->_store) && $store->getId()) {
                $this->_store = $store;
            }

            if (0 == $store->getId()) {
                $store->setUrlModel($this->_url);
            }
        }

        foreach ($groupCollection as $group) {
            /* @var $group Group */
            if (!isset($groupStores[$group->getId()])) {
                $groupStores[$group->getId()] = array();
            }
            $group->setStores($groupStores[$group->getId()]);
            $group->setWebsite($websiteCollection->getItemById($group->getWebsiteId()));

            $websiteGroups[$group->getWebsiteId()][$group->getId()] = $group;

            $this->_groups[$group->getId()] = $group;
        }

        foreach ($websiteCollection as $website) {
            /* @var $website Website */
            if (!isset($websiteGroups[$website->getId()])) {
                $websiteGroups[$website->getId()] = array();
            }
            if (!isset($websiteStores[$website->getId()])) {
                $websiteStores[$website->getId()] = array();
            }
            if ($website->getIsDefault()) {
                $this->_website = $website;
            }
            $website->setGroups($websiteGroups[$website->getId()]);
            $website->setStores($websiteStores[$website->getId()]);

            $this->_websites[$website->getId()] = $website;
            $this->_websites[$website->getCode()] = $website;
        }
    }

    /**
     * Allow or disallow single store mode
     *
     * @param bool $value
     * @return void
     */
    public function setIsSingleStoreModeAllowed($value)
    {
        $this->_isSingleStoreAllowed = (bool)$value;
    }

    /**
     * Check if store has only one store view
     *
     * @return bool
     */
    public function hasSingleStore()
    {
        return $this->_hasSingleStore;
    }

    /**
     * Retrieve application store object
     *
     * @param null|string|bool|int|Store $storeId
     * @return Store
     * @throws StoreException
     */
    public function getStore($storeId = null)
    {
        if ($this->_appState->getUpdateMode()) {
            return $this->_getDefaultStore();
        }

        if ($storeId === true && $this->hasSingleStore()) {
            return $this->_store;
        }

        if (!isset($storeId) || '' === $storeId || $storeId === true) {
            $storeId = $this->_currentStore;
        }
        if ($storeId instanceof Store) {
            return $storeId;
        }
        if (!isset($storeId)) {
            $this->throwStoreException();
        }

        if (empty($this->_stores[$storeId])) {
            $store = $this->_storeFactory->create();
            if (is_numeric($storeId)) {
                $store->load($storeId);
            } elseif (is_string($storeId)) {
                $store->load($storeId, 'code');
            }

            if (!$store->getCode()) {
                $this->throwStoreException();
            }
            $this->_stores[$store->getStoreId()] = $store;
            $this->_stores[$store->getCode()] = $store;
        }
        return $this->_stores[$storeId];
    }

    /**
     * Retrieve stores array
     *
     * @param bool $withDefault
     * @param bool $codeKey
     * @return Store[]
     */
    public function getStores($withDefault = false, $codeKey = false)
    {
        $stores = array();
        foreach ($this->_stores as $store) {
            if (!$withDefault && $store->getId() == 0) {
                continue;
            }
            if ($codeKey) {
                $stores[$store->getCode()] = $store;
            } else {
                $stores[$store->getId()] = $store;
            }
        }

        return $stores;
    }

    /**
     * Retrieve application website object
     *
     * @param null|bool|int|string|Website $websiteId
     * @return Website
     * @throws Exception
     */
    public function getWebsite($websiteId = null)
    {
        if ($websiteId === null || $websiteId === '') {
            $websiteId = $this->getStore()->getWebsiteId();
        } elseif ($websiteId instanceof Website) {
            return $websiteId;
        } elseif ($websiteId === true) {
            return $this->_website;
        }

        if (empty($this->_websites[$websiteId])) {
            $website = $this->_websiteFactory->create();
            // load method will load website by code if given ID is not a numeric value
            $website->load($websiteId);
            if (!$website->hasWebsiteId()) {
                throw new Exception('Invalid website id/code requested.');
            }
            $this->_websites[$website->getWebsiteId()] = $website;
            $this->_websites[$website->getCode()] = $website;
        }
        return $this->_websites[$websiteId];
    }

    /**
     * Get loaded websites
     *
     * @param bool $withDefault
     * @param bool|string $codeKey
     * @return Website[]
     */
    public function getWebsites($withDefault = false, $codeKey = false)
    {
        $websites = array();
        if (is_array($this->_websites)) {
            foreach ($this->_websites as $website) {
                if (!$withDefault && $website->getId() == 0) {
                    continue;
                }
                if ($codeKey) {
                    $websites[$website->getCode()] = $website;
                } else {
                    $websites[$website->getId()] = $website;
                }
            }
        }
        return $websites;
    }

    /**
     * Retrieve application store group object
     *
     * @param null|Group|string $groupId
     * @return Group
     * @throws Exception
     */
    public function getGroup($groupId = null)
    {
        if (is_null($groupId)) {
            $groupId = $this->getStore()->getGroupId();
        } elseif ($groupId instanceof Group) {
            return $groupId;
        }
        if (empty($this->_groups[$groupId])) {
            $group = $this->_groupFactory->create();
            if (is_numeric($groupId)) {
                $group->load($groupId);
                if (!$group->hasGroupId()) {
                    throw new Exception('Invalid store group id requested.');
                }
            }
            $this->_groups[$group->getGroupId()] = $group;
        }
        return $this->_groups[$groupId];
    }

    /**
     * Prepare array of store groups
     * can be filtered to contain default store group or not by $withDefault flag
     * depending on flag $codeKey array keys can be group id or group code
     *
     * @param bool $withDefault
     * @param bool $codeKey
     * @return Group
     */
    public function getGroups($withDefault = false, $codeKey = false)
    {
        $groups = array();
        if (is_array($this->_groups)) {
            foreach ($this->_groups as $group) {
                /** @var $group Group */
                if (!$withDefault && $group->getId() == 0) {
                    continue;
                }
                if ($codeKey) {
                    $groups[$group->getCode()] = $group;
                } else {
                    $groups[$group->getId()] = $group;
                }
            }
        }
        return $groups;
    }

    /**
     * Reinitialize store list
     *
     * @return void
     */
    public function reinitStores()
    {
        $this->_initStores();
    }

    /**
     * Retrieve default store for default group and website
     *
     * @return Store|null
     */
    public function getDefaultStoreView()
    {
        foreach ($this->getWebsites() as $_website) {
            /** @var $_website Website */
            if ($_website->getIsDefault()) {
                $_defaultStore = $this->getGroup($_website->getDefaultGroupId())->getDefaultStore();
                if ($_defaultStore) {
                    return $_defaultStore;
                }
            }
        }
        return null;
    }

    /**
     *  Unset website by id from app cache
     *
     * @param null|bool|int|string|Website $websiteId
     * @return void
     */
    public function clearWebsiteCache($websiteId = null)
    {
        if (is_null($websiteId)) {
            $websiteId = $this->getStore()->getWebsiteId();
        } elseif ($websiteId instanceof Website) {
            $websiteId = $websiteId->getId();
        } elseif ($websiteId === true) {
            $websiteId = $this->_website->getId();
        }

        if (!empty($this->_websites[$websiteId])) {
            $website = $this->_websites[$websiteId];

            unset($this->_websites[$website->getWebsiteId()]);
            unset($this->_websites[$website->getCode()]);
        }
    }

    /**
     * Get either default or any store view
     *
     * @return Store|null
     */
    public function getAnyStoreView()
    {
        $store = $this->getDefaultStoreView();
        if ($store) {
            return $store;
        }
        foreach ($this->getStores() as $store) {
            return $store;
        }

        return null;
    }

    /**
     * Set current default store
     *
     * @param string $store
     * @return void
     */
    public function setCurrentStore($store)
    {
        $this->_currentStore = $store;
    }

    /**
     * @return void
     * @throws StoreException
     */
    public function throwStoreException()
    {
        throw new StoreException('');
    }

    /**
     * Get current store code
     *
     * @return string
     */
    public function getCurrentStore()
    {
        return $this->_currentStore;
    }
}
