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
 * @category    Magento
 * @package     Magento_Catalog
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Catalog\Model;

/**
 * Catalog product model
 *
 * @method Resource\Product getResource()
 * @method Resource\Product _getResource()
 * @method \Magento\Catalog\Model\Product setHasError(bool $value)
 * @method null|bool getHasError()
 * @method \Magento\Catalog\Model\Product setTypeId(string $typeId)
 * @method \Magento\Catalog\Model\Product setAssociatedProductIds(array $productIds)
 * @method array getAssociatedProductIds()
 * @method \Magento\Catalog\Model\Product setNewVariationsAttributeSetId(int $value)
 * @method int getNewVariationsAttributeSetId()
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */

class Product extends \Magento\Catalog\Model\AbstractModel implements \Magento\Object\IdentityInterface
{
    /**
     * Entity code.
     * Can be used as part of method name for entity processing
     */
    const ENTITY                 = 'catalog_product';

    const CACHE_TAG              = 'catalog_product';

    /**
     * @var string
     */
    protected $_cacheTag         = 'catalog_product';

    /**
     * @var string
     */
    protected $_eventPrefix      = 'catalog_product';

    /**
     * @var string
     */
    protected $_eventObject      = 'product';

    /**
     * @var bool
     */
    protected $_canAffectOptions = false;

    /**
     * Product type singleton instance
     *
     * @var \Magento\Catalog\Model\Product\Type\AbstractType
     */
    protected $_typeInstance = null;

    /**
     * Product link instance
     *
     * @var Product\Link
     */
    protected $_linkInstance;

    /**
     * Product object customization (not stored in DB)
     *
     * @var array
     */
    protected $_customOptions = array();

    /**
     * Product Url Instance
     *
     * @var Product\Url
     */
    protected $_urlModel = null;

    /**
     * @var string
     */
    protected static $_url;

    /**
     * @var string
     */
    protected static $_urlRewrite;

    /**
     * @var array
     */
    protected $_errors = array();

    /**
     * Product option
     *
     * @var Product\Option
     */
    protected $_optionInstance;

    /**
     * @var array
     */
    protected $_options = array();

    /**
     * Product reserved attribute codes
     *
     * @var mixed
     */
    protected $_reservedAttributes;

    /**
     * Flag for available duplicate function
     *
     * @var boolean
     */
    protected $_isDuplicable = true;

    /**
     * Flag for get Price function
     *
     * @var boolean
     */
    protected $_calculatePrice = true;

    /**
     * Catalog product
     *
     * @var \Magento\Catalog\Helper\Product
     */
    protected $_catalogProduct = null;

    /**
     * Catalog data
     *
     * @var \Magento\Catalog\Helper\Data
     */
    protected $_catalogData = null;

    /**
     * Catalog image
     *
     * @var \Magento\Catalog\Helper\Image
     */
    protected $_catalogImage = null;

    /**
     * @var \Magento\Data\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * Catalog product type
     *
     * @var Product\Type
     */
    protected $_catalogProductType;

    /**
     * Index indexer
     *
     * @var \Magento\Index\Model\Indexer
     */
    protected $_indexIndexer;

    /**
     * Catalog product media config
     *
     * @var Product\Media\Config
     */
    protected $_catalogProductMediaConfig;

    /**
     * Catalog product status
     *
     * @var \Magento\Catalog\Model\Product\Attribute\Source\Status
     */
    protected $_catalogProductStatus;

    /**
     * Catalog product visibility
     *
     * @var Product\Visibility
     */
    protected $_catalogProductVisibility;

    /**
     * Category factory
     *
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $_categoryFactory;

    /**
     * Product factory
     *
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * Stock item factory
     *
     * @var \Magento\CatalogInventory\Model\Stock\ItemFactory
     */
    protected $_stockItemFactory;

    /**
     * Item option factory
     *
     * @var \Magento\Catalog\Model\Product\Configuration\Item\OptionFactory
     */
    protected $_itemOptionFactory;

    /**
     * Filesystem facade
     *
     * @var \Magento\App\Filesystem
     */
    protected $_filesystem;

    /**
     * @var \Magento\Indexer\Model\IndexerInterface
     */
    protected $categoryIndexer;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Flat\Processor
     */
    protected $_productFlatIndexerProcessor;

    /**
     * @param \Magento\Model\Context $context
     * @param \Magento\Registry $registry
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param Product\Url $url
     * @param Product\Link $productLink
     * @param Product\Configuration\Item\OptionFactory $itemOptionFactory
     * @param \Magento\CatalogInventory\Model\Stock\ItemFactory $stockItemFactory
     * @param CategoryFactory $categoryFactory
     * @param Product\Option $catalogProductOption
     * @param Product\Visibility $catalogProductVisibility
     * @param Product\Attribute\Source\Status $catalogProductStatus
     * @param Product\Media\Config $catalogProductMediaConfig
     * @param \Magento\Index\Model\Indexer $indexIndexer
     * @param Product\Type $catalogProductType
     * @param \Magento\Catalog\Helper\Image $catalogImage
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param \Magento\Catalog\Helper\Product $catalogProduct
     * @param Resource\Product $resource
     * @param Resource\Product\Collection $resourceCollection
     * @param \Magento\Data\CollectionFactory $collectionFactory
     * @param \Magento\App\Filesystem $filesystem
     * @param \Magento\Indexer\Model\IndexerInterface $categoryIndexer
     * @param Indexer\Product\Flat\Processor $productFlatIndexerProcessor
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Model\Context $context,
        \Magento\Registry $registry,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        Product\Url $url,
        Product\Link $productLink,
        \Magento\Catalog\Model\Product\Configuration\Item\OptionFactory $itemOptionFactory,
        \Magento\CatalogInventory\Model\Stock\ItemFactory $stockItemFactory,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Catalog\Model\Product\Option $catalogProductOption,
        \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility,
        \Magento\Catalog\Model\Product\Attribute\Source\Status $catalogProductStatus,
        \Magento\Catalog\Model\Product\Media\Config $catalogProductMediaConfig,
        \Magento\Index\Model\Indexer $indexIndexer,
        Product\Type $catalogProductType,
        \Magento\Catalog\Helper\Image $catalogImage,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\Catalog\Helper\Product $catalogProduct,
        Resource\Product $resource,
        Resource\Product\Collection $resourceCollection,
        \Magento\Data\CollectionFactory $collectionFactory,
        \Magento\App\Filesystem $filesystem,
        \Magento\Indexer\Model\IndexerInterface $categoryIndexer,
        \Magento\Catalog\Model\Indexer\Product\Flat\Processor $productFlatIndexerProcessor,
        array $data = array()
    ) {
        $this->_itemOptionFactory = $itemOptionFactory;
        $this->_stockItemFactory = $stockItemFactory;
        $this->_categoryFactory = $categoryFactory;
        $this->_optionInstance = $catalogProductOption;
        $this->_catalogProductVisibility = $catalogProductVisibility;
        $this->_catalogProductStatus = $catalogProductStatus;
        $this->_catalogProductMediaConfig = $catalogProductMediaConfig;
        $this->_indexIndexer = $indexIndexer;
        $this->_catalogProductType = $catalogProductType;
        $this->_catalogImage = $catalogImage;
        $this->_catalogData = $catalogData;
        $this->_catalogProduct = $catalogProduct;
        $this->_collectionFactory = $collectionFactory;
        $this->_urlModel = $url;
        $this->_linkInstance = $productLink;
        $this->_filesystem = $filesystem;
        $this->categoryIndexer = $categoryIndexer;
        $this->_productFlatIndexerProcessor = $productFlatIndexerProcessor;
        parent::__construct($context, $registry, $storeManager, $resource, $resourceCollection, $data);
    }

    /**
     * Initialize resources
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Catalog\Model\Resource\Product');
    }

    /**
     * Return product category indexer object
     *
     * @return \Magento\Indexer\Model\IndexerInterface
     */
    protected function getCategoryIndexer()
    {
        if (!$this->categoryIndexer->getId()) {
            $this->categoryIndexer->load(Indexer\Product\Category::INDEXER_ID);
        }
        return $this->categoryIndexer;
    }

    /**
     * Retrieve Store Id
     *
     * @return int
     */
    public function getStoreId()
    {
        if ($this->hasData('store_id')) {
            return $this->getData('store_id');
        }
        return $this->_storeManager->getStore()->getId();
    }

    /**
     * Get collection instance
     *
     * @return object
     */
    public function getResourceCollection()
    {
        $collection = parent::getResourceCollection();
        $collection->setStoreId($this->getStoreId());
        return $collection;
    }

    /**
     * Get product url model
     *
     * @return Product\Url
     */
    public function getUrlModel()
    {
        return $this->_urlModel;
    }

    /**
     * Validate Product Data
     *
     * @todo implement full validation process with errors returning which are ignoring now
     *
     * @return array
     */
    public function validate()
    {
        $this->_eventManager->dispatch($this->_eventPrefix . '_validate_before', $this->_getEventData());
        $result = $this->_getResource()->validate($this);
        $this->_eventManager->dispatch($this->_eventPrefix . '_validate_after', $this->_getEventData());
        return $result;
    }

    /**
     * Get product name
     *
     * @return string
     */
    public function getName()
    {
        return $this->_getData('name');
    }

    /**
     * Get product price through type instance
     *
     * @return float
     */
    public function getPrice()
    {
        if ($this->_calculatePrice || !$this->getData('price')) {
            return $this->getPriceModel()->getPrice($this);
        } else {
            return $this->getData('price');
        }
    }

    /**
     * Set Price calculation flag
     *
     * @param bool $calculate
     * @return void
     */
    public function setPriceCalculation($calculate = true)
    {
        $this->_calculatePrice = $calculate;
    }

    /**
     * Get product type identifier
     *
     * @return string
     */
    public function getTypeId()
    {
        return $this->_getData('type_id');
    }

    /**
     * Get product status
     *
     * @return int
     */
    public function getStatus()
    {
        if (is_null($this->_getData('status'))) {
            $this->setData('status', \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
        }
        return $this->_getData('status');
    }

    /**
     * Retrieve type instance of the product.
     * Type instance implements product type depended logic and is a singleton shared by all products of the same type.
     *
     * @return \Magento\Catalog\Model\Product\Type\AbstractType
     */
    public function getTypeInstance()
    {
        if ($this->_typeInstance === null) {
            $this->_typeInstance = $this->_catalogProductType
                ->factory($this);
        }
        return $this->_typeInstance;
    }

    /**
     * Set type instance for the product
     *
     * @param \Magento\Catalog\Model\Product\Type\AbstractType|null $instance  Product type instance
     * @return \Magento\Catalog\Model\Product
     */
    public function setTypeInstance($instance)
    {
        $this->_typeInstance = $instance;
        return $this;
    }

    /**
     * Retrieve link instance
     *
     * @return  Product\Link
     */
    public function getLinkInstance()
    {
        return $this->_linkInstance;
    }

    /**
     * Retrieve product id by sku
     *
     * @param   string $sku
     * @return  integer
     */
    public function getIdBySku($sku)
    {
        return $this->_getResource()->getIdBySku($sku);
    }

    /**
     * Retrieve product category id
     *
     * @return int
     */
    public function getCategoryId()
    {
        $category = $this->_coreRegistry->registry('current_category');
        if ($category) {
            return $category->getId();
        }
        return false;
    }

    /**
     * Retrieve product category
     *
     * @return \Magento\Catalog\Model\Category
     */
    public function getCategory()
    {
        $category = $this->getData('category');
        if (is_null($category) && $this->getCategoryId()) {
            $category = $this->_categoryFactory->create()->load($this->getCategoryId());
            $this->setCategory($category);
        }
        return $category;
    }

    /**
     * Retrieve assigned category Ids
     *
     * @return array
     */
    public function getCategoryIds()
    {
        if (!$this->hasData('category_ids')) {
            $wasLocked = false;
            if ($this->isLockedAttribute('category_ids')) {
                $wasLocked = true;
                $this->unlockAttribute('category_ids');
            }
            $ids = $this->_getResource()->getCategoryIds($this);
            $this->setData('category_ids', $ids);
            if ($wasLocked) {
                $this->lockAttribute('category_ids');
            }
        }

        return (array)$this->_getData('category_ids');
    }

    /**
     * Retrieve product categories
     *
     * @return \Magento\Data\Collection
     */
    public function getCategoryCollection()
    {
        return $this->_getResource()->getCategoryCollection($this);
    }

    /**
     * Retrieve product websites identifiers
     *
     * @return array
     */
    public function getWebsiteIds()
    {
        if (!$this->hasWebsiteIds()) {
            $ids = $this->_getResource()->getWebsiteIds($this);
            $this->setWebsiteIds($ids);
        }
        return $this->getData('website_ids');
    }

    /**
     * Get all sore ids where product is presented
     *
     * @return array
     */
    public function getStoreIds()
    {
        if (!$this->hasStoreIds()) {
            $storeIds = array();
            if ($websiteIds = $this->getWebsiteIds()) {
                foreach ($websiteIds as $websiteId) {
                    $websiteStores = $this->_storeManager->getWebsite($websiteId)->getStoreIds();
                    $storeIds = array_merge($storeIds, $websiteStores);
                }
            }
            $this->setStoreIds($storeIds);
        }
        return $this->getData('store_ids');
    }

    /**
     * Retrieve product attributes
     * if $groupId is null - retrieve all product attributes
     *
     * @param int  $groupId   Retrieve attributes of the specified group
     * @param bool $skipSuper Not used
     * @return array
     */
    public function getAttributes($groupId = null, $skipSuper = false)
    {
        $productAttributes = $this->getTypeInstance()->getEditableAttributes($this);
        if ($groupId) {
            $attributes = array();
            foreach ($productAttributes as $attribute) {
                if ($attribute->isInGroup($this->getAttributeSetId(), $groupId)) {
                    $attributes[] = $attribute;
                }
            }
        } else {
            $attributes = $productAttributes;
        }

        return $attributes;
    }

    /**
     * Check product options and type options and save them, too
     *
     * @return void
     */
    protected function _beforeSave()
    {
        $this->cleanCache();
        $this->setTypeHasOptions(false);
        $this->setTypeHasRequiredOptions(false);

        $this->getTypeInstance()->beforeSave($this);

        $hasOptions         = false;
        $hasRequiredOptions = false;

        /**
         * $this->_canAffectOptions - set by type instance only
         * $this->getCanSaveCustomOptions() - set either in controller when "Custom Options" ajax tab is loaded,
         * or in type instance as well
         */
        $this->canAffectOptions($this->_canAffectOptions && $this->getCanSaveCustomOptions());
        if ($this->getCanSaveCustomOptions()) {
            $options = $this->getProductOptions();
            if (is_array($options)) {
                $this->setIsCustomOptionChanged(true);
                foreach ($this->getProductOptions() as $option) {
                    $this->getOptionInstance()->addOption($option);
                    if ((!isset($option['is_delete'])) || $option['is_delete'] != '1') {
                        $hasOptions = true;
                    }
                }
                foreach ($this->getOptionInstance()->getOptions() as $option) {
                    if ($option['is_require'] == '1') {
                        $hasRequiredOptions = true;
                        break;
                    }
                }
            }
        }

        /**
         * Set true, if any
         * Set false, ONLY if options have been affected by Options tab and Type instance tab
         */
        if ($hasOptions || (bool)$this->getTypeHasOptions()) {
            $this->setHasOptions(true);
            if ($hasRequiredOptions || (bool)$this->getTypeHasRequiredOptions()) {
                $this->setRequiredOptions(true);
            } elseif ($this->canAffectOptions()) {
                $this->setRequiredOptions(false);
            }
        } elseif ($this->canAffectOptions()) {
            $this->setHasOptions(false);
            $this->setRequiredOptions(false);
        }

        parent::_beforeSave();
    }

    /**
     * Check/set if options can be affected when saving product
     * If value specified, it will be set.
     *
     * @param   bool $value
     * @return  bool
     */
    public function canAffectOptions($value = null)
    {
        if (null !== $value) {
            $this->_canAffectOptions = (bool)$value;
        }
        return $this->_canAffectOptions;
    }

    /**
     * Saving product type related data and init index
     *
     * @return \Magento\Catalog\Model\Product
     */
    protected function _afterSave()
    {
        $this->getLinkInstance()->saveProductRelations($this);
        $this->getTypeInstance()->save($this);

        /**
         * Product Options
         */
        $this->getOptionInstance()->setProduct($this)
            ->saveOptions();

        $result = parent::_afterSave();

        $this->_indexIndexer->processEntityAction(
            $this, self::ENTITY, \Magento\Index\Model\Event::TYPE_SAVE
        );
        $this->_getResource()->addCommitCallback(array($this, 'reindex'));
        return $result;
    }


    /**
     * Init indexing process after product save
     *
     * @return $this
     */
    public function reindex()
    {
        $this->_productFlatIndexerProcessor->reindexRow($this->getEntityId());
        if (!$this->getCategoryIndexer()->isScheduled()) {
            $this->getCategoryIndexer()->reindexRow($this->getId());
        }
    }

    /**
     * Clear cache related with product and protect delete from not admin
     * Register indexing event before delete product
     *
     * @return \Magento\Catalog\Model\Product
     */
    protected function _beforeDelete()
    {
        $this->_protectFromNonAdmin();
        $this->cleanCache();
        $this->_indexIndexer->logEvent(
            $this, self::ENTITY, \Magento\Index\Model\Event::TYPE_DELETE
        );
        return parent::_beforeDelete();
    }

    /**
     * Init indexing process after product delete commit
     *
     * @return void
     */
    protected function _afterDeleteCommit()
    {
        $this->reindex();
        parent::_afterDeleteCommit();
        $this->_indexIndexer->indexEvents(
            self::ENTITY, \Magento\Index\Model\Event::TYPE_DELETE
        );
    }

    /**
     * Load product options if they exists
     *
     * @return $this
     */
    protected function _afterLoad()
    {
        parent::_afterLoad();
        /**
         * Load product options
         */
        if ($this->getHasOptions()) {
            foreach ($this->getProductOptionsCollection() as $option) {
                $option->setProduct($this);
                $this->addOption($option);
            }
        }
        return $this;
    }

    /**
     * Clear cache related with product id
     *
     * @return $this
     */
    public function cleanCache()
    {
        $this->_cacheManager->clean('catalog_product_'.$this->getId());
        return $this;
    }

    /**
     * Get product price model
     *
     * @return \Magento\Catalog\Model\Product\Type\Price
     */
    public function getPriceModel()
    {
        return $this->_catalogProductType->priceFactory($this->getTypeId());
    }

    /**
     * Get product group price
     *
     * @return float
     */
    public function getGroupPrice()
    {
        return $this->getPriceModel()->getGroupPrice($this);
    }

    /**
     * Get product tier price by qty
     *
     * @param   float $qty
     * @return  float|array
     */
    public function getTierPrice($qty=null)
    {
        return $this->getPriceModel()->getTierPrice($qty, $this);
    }

    /**
     * Count how many tier prices we have for the product
     *
     * @return  int
     */
    public function getTierPriceCount()
    {
        return $this->getPriceModel()->getTierPriceCount($this);
    }

    /**
     * Get formated by currency tier price
     *
     * @param   float $qty
     * @return  array || double
     */
    public function getFormatedTierPrice($qty=null)
    {
        return $this->getPriceModel()->getFormatedTierPrice($qty, $this);
    }

    /**
     * Get formated by currency product price
     *
     * @return  array || double
     */
    public function getFormatedPrice()
    {
        return $this->getPriceModel()->getFormatedPrice($this);
    }

    /**
     * Sets final price of product
     *
     * This func is equal to magic 'setFinalPrice()', but added as a separate func, because in cart with bundle
     * products it's called very often in Item->getProduct(). So removing chain of magic with more cpu consuming
     * algorithms gives nice optimization boost.
     *
     * @param float $price Price amount
     * @return \Magento\Catalog\Model\Product
     */
    public function setFinalPrice($price)
    {
        $this->_data['final_price'] = $price;
        return $this;
    }

    /**
     * Get product final price
     *
     * @param float $qty
     * @return float
     */
    public function getFinalPrice($qty=null)
    {
        $price = $this->_getData('final_price');
        if ($price !== null) {
            return $price;
        }
        return $this->getPriceModel()->getFinalPrice($qty, $this);
    }

    /**
     * Returns calculated final price
     *
     * @return float
     */
    public function getCalculatedFinalPrice()
    {
        return $this->_getData('calculated_final_price');
    }

    /**
     * Returns minimal price
     *
     * @return float
     */
    public function getMinimalPrice()
    {
        return max($this->_getData('minimal_price'), 0);
    }

    /**
     * Returns special price
     *
     * @return float
     */
    public function getSpecialPrice()
    {
        return $this->_getData('special_price');
    }

    /**
     * Returns starting date of the special price
     *
     * @return mixed
     */
    public function getSpecialFromDate()
    {
        return $this->_getData('special_from_date');
    }

    /**
     * Returns end date of the special price
     *
     * @return mixed
     */
    public function getSpecialToDate()
    {
        return $this->_getData('special_to_date');
    }


/*******************************************************************************
 ** Linked products API
 */
    /**
     * Retrieve array of related products
     *
     * @return array
     */
    public function getRelatedProducts()
    {
        if (!$this->hasRelatedProducts()) {
            $products = array();
            $collection = $this->getRelatedProductCollection();
            foreach ($collection as $product) {
                $products[] = $product;
            }
            $this->setRelatedProducts($products);
        }
        return $this->getData('related_products');
    }

    /**
     * Retrieve related products identifiers
     *
     * @return array
     */
    public function getRelatedProductIds()
    {
        if (!$this->hasRelatedProductIds()) {
            $ids = array();
            foreach ($this->getRelatedProducts() as $product) {
                $ids[] = $product->getId();
            }
            $this->setRelatedProductIds($ids);
        }
        return $this->getData('related_product_ids');
    }

    /**
     * Retrieve collection related product
     *
     * @return \Magento\Catalog\Model\Resource\Product\Link\Product\Collection
     */
    public function getRelatedProductCollection()
    {
        $collection = $this->getLinkInstance()->useRelatedLinks()
            ->getProductCollection()
            ->setIsStrongMode();
        $collection->setProduct($this);
        return $collection;
    }

    /**
     * Retrieve collection related link
     *
     * @return \Magento\Catalog\Model\Resource\Product\Link\Collection
     */
    public function getRelatedLinkCollection()
    {
        $collection = $this->getLinkInstance()->useRelatedLinks()
            ->getLinkCollection();
        $collection->setProduct($this);
        $collection->addLinkTypeIdFilter();
        $collection->addProductIdFilter();
        $collection->joinAttributes();
        return $collection;
    }

    /**
     * Retrieve array of up sell products
     *
     * @return array
     */
    public function getUpSellProducts()
    {
        if (!$this->hasUpSellProducts()) {
            $products = array();
            foreach ($this->getUpSellProductCollection() as $product) {
                $products[] = $product;
            }
            $this->setUpSellProducts($products);
        }
        return $this->getData('up_sell_products');
    }

    /**
     * Retrieve up sell products identifiers
     *
     * @return array
     */
    public function getUpSellProductIds()
    {
        if (!$this->hasUpSellProductIds()) {
            $ids = array();
            foreach ($this->getUpSellProducts() as $product) {
                $ids[] = $product->getId();
            }
            $this->setUpSellProductIds($ids);
        }
        return $this->getData('up_sell_product_ids');
    }

    /**
     * Retrieve collection up sell product
     *
     * @return \Magento\Catalog\Model\Resource\Product\Link\Product\Collection
     */
    public function getUpSellProductCollection()
    {
        $collection = $this->getLinkInstance()->useUpSellLinks()
            ->getProductCollection()
            ->setIsStrongMode();
        $collection->setProduct($this);
        return $collection;
    }

    /**
     * Retrieve collection up sell link
     *
     * @return \Magento\Catalog\Model\Resource\Product\Link\Collection
     */
    public function getUpSellLinkCollection()
    {
        $collection = $this->getLinkInstance()->useUpSellLinks()
            ->getLinkCollection();
        $collection->setProduct($this);
        $collection->addLinkTypeIdFilter();
        $collection->addProductIdFilter();
        $collection->joinAttributes();
        return $collection;
    }

    /**
     * Retrieve array of cross sell products
     *
     * @return array
     */
    public function getCrossSellProducts()
    {
        if (!$this->hasCrossSellProducts()) {
            $products = array();
            foreach ($this->getCrossSellProductCollection() as $product) {
                $products[] = $product;
            }
            $this->setCrossSellProducts($products);
        }
        return $this->getData('cross_sell_products');
    }

    /**
     * Retrieve cross sell products identifiers
     *
     * @return array
     */
    public function getCrossSellProductIds()
    {
        if (!$this->hasCrossSellProductIds()) {
            $ids = array();
            foreach ($this->getCrossSellProducts() as $product) {
                $ids[] = $product->getId();
            }
            $this->setCrossSellProductIds($ids);
        }
        return $this->getData('cross_sell_product_ids');
    }

    /**
     * Retrieve collection cross sell product
     *
     * @return \Magento\Catalog\Model\Resource\Product\Link\Product\Collection
     */
    public function getCrossSellProductCollection()
    {
        $collection = $this->getLinkInstance()->useCrossSellLinks()
            ->getProductCollection()
            ->setIsStrongMode();
        $collection->setProduct($this);
        return $collection;
    }

    /**
     * Retrieve collection cross sell link
     *
     * @return \Magento\Catalog\Model\Resource\Product\Link\Collection
     */
    public function getCrossSellLinkCollection()
    {
        $collection = $this->getLinkInstance()->useCrossSellLinks()
            ->getLinkCollection();
        $collection->setProduct($this);
        $collection->addLinkTypeIdFilter();
        $collection->addProductIdFilter();
        $collection->joinAttributes();
        return $collection;
    }
/*******************************************************************************
 ** Media API
 */
    /**
     * Retrieve attributes for media gallery
     *
     * @return array
     */
    public function getMediaAttributes()
    {
        if (!$this->hasMediaAttributes()) {
            $mediaAttributes = array();
            foreach ($this->getAttributes() as $attribute) {
                if($attribute->getFrontend()->getInputType() == 'media_image') {
                    $mediaAttributes[$attribute->getAttributeCode()] = $attribute;
                }
            }
            $this->setMediaAttributes($mediaAttributes);
        }
        return $this->getData('media_attributes');
    }

    /**
     * Retrieve media gallery images
     *
     * @return \Magento\Data\Collection
     */
    public function getMediaGalleryImages()
    {
        $directory = $this->_filesystem->getDirectoryRead(\Magento\App\Filesystem::MEDIA_DIR);
        if(!$this->hasData('media_gallery_images') && is_array($this->getMediaGallery('images'))) {
            $images = $this->_collectionFactory->create();
            foreach ($this->getMediaGallery('images') as $image) {
                if (isset($image['disabled']) && $image['disabled']) {
                    continue;
                }
                $image['url'] = $this->getMediaConfig()->getMediaUrl($image['file']);
                $image['id'] = isset($image['value_id']) ? $image['value_id'] : null;
                $image['path'] = $directory->getAbsolutePath($this->getMediaConfig()->getMediaPath($image['file']));
                $images->addItem(new \Magento\Object($image));
            }
            $this->setData('media_gallery_images', $images);
        }

        return $this->getData('media_gallery_images');
    }

    /**
     * Add image to media gallery
     *
     * @param string        $file              file path of image in file system
     * @param string|array  $mediaAttribute    code of attribute with type 'media_image',
     *                                          leave blank if image should be only in gallery
     * @param boolean       $move              if true, it will move source file
     * @param boolean       $exclude           mark image as disabled in product page view
     * @return \Magento\Catalog\Model\Product
     */
    public function addImageToMediaGallery($file, $mediaAttribute=null, $move=false, $exclude=true)
    {
        $attributes = $this->getTypeInstance()->getSetAttributes($this);
        if (!isset($attributes['media_gallery'])) {
            return $this;
        }
        $mediaGalleryAttribute = $attributes['media_gallery'];
        /* @var $mediaGalleryAttribute \Magento\Catalog\Model\Resource\Eav\Attribute */
        $mediaGalleryAttribute->getBackend()->addImage($this, $file, $mediaAttribute, $move, $exclude);
        return $this;
    }

    /**
     * Retrieve product media config
     *
     * @return Product\Media\Config
     */
    public function getMediaConfig()
    {
        return $this->_catalogProductMediaConfig;
    }

    /**
     * Returns visible status IDs in catalog
     *
     * @return array
     */
    public function getVisibleInCatalogStatuses()
    {
        return $this->_catalogProductStatus->getVisibleStatusIds();
    }

    /**
     * Retrieve visible statuses
     *
     * @return array
     */
    public function getVisibleStatuses()
    {
        return $this->_catalogProductStatus->getVisibleStatusIds();
    }

    /**
     * Check Product visible in catalog
     *
     * @return bool
     */
    public function isVisibleInCatalog()
    {
        return in_array($this->getStatus(), $this->getVisibleInCatalogStatuses());
    }

    /**
     * Retrieve visible in site visibilities
     *
     * @return array
     */
    public function getVisibleInSiteVisibilities()
    {
        return $this->_catalogProductVisibility->getVisibleInSiteIds();
    }

    /**
     * Check Product visible in site
     *
     * @return bool
     */
    public function isVisibleInSiteVisibility()
    {
        return in_array($this->getVisibility(), $this->getVisibleInSiteVisibilities());
    }

    /**
     * Checks product can be duplicated
     *
     * @return boolean
     */
    public function isDuplicable()
    {
        return $this->_isDuplicable;
    }

    /**
     * Set is duplicable flag
     *
     * @param boolean $value
     * @return \Magento\Catalog\Model\Product
     */
    public function setIsDuplicable($value)
    {
        $this->_isDuplicable = (boolean) $value;
        return $this;
    }


    /**
     * Check is product available for sale
     *
     * @return bool
     */
    public function isSalable()
    {
        $this->_eventManager->dispatch('catalog_product_is_salable_before', array(
            'product'   => $this
        ));

        $salable = $this->isAvailable();

        $object = new \Magento\Object(array(
            'product'    => $this,
            'is_salable' => $salable
        ));
        $this->_eventManager->dispatch('catalog_product_is_salable_after', array(
            'product'   => $this,
            'salable'   => $object
        ));
        return $object->getIsSalable();
    }

    /**
     * Check whether the product type or stock allows to purchase the product
     *
     * @return bool
     */
    public function isAvailable()
    {
        return $this->getTypeInstance()->isSalable($this)
            || $this->_catalogProduct->getSkipSaleableCheck();
    }

    /**
     * Is product salable detecting by product type
     *
     * @return bool
     */
    public function getIsSalable()
    {
        $productType = $this->getTypeInstance();
        if (method_exists($productType, 'getIsSalable')) {
            return $productType->getIsSalable($this);
        }
        if ($this->hasData('is_salable')) {
            return $this->getData('is_salable');
        }

        return $this->isSalable();
    }

    /**
     * Check is a virtual product
     * Data helper wrapper
     *
     * @return bool
     */
    public function isVirtual()
    {
        return $this->getIsVirtual();
    }

    /**
     * Whether the product is a recurring payment
     *
     * @return bool
     */
    public function isRecurring()
    {
        return $this->getIsRecurring() == '1';
    }

    /**
     * Alias for isSalable()
     *
     * @return bool
     */
    public function isSaleable()
    {
        return $this->isSalable();
    }

    /**
     * Whether product available in stock
     *
     * @return bool
     */
    public function isInStock()
    {
        return $this->getStatus() == \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED;
    }

    /**
     * Get attribute text by its code
     *
     * @param string $attributeCode Code of the attribute
     * @return string
     */
    public function getAttributeText($attributeCode)
    {
        return $this->getResource()
            ->getAttribute($attributeCode)
            ->getSource()
            ->getOptionText($this->getData($attributeCode));
    }

    /**
     * Returns array with dates for custom design
     *
     * @return array
     */
    public function getCustomDesignDate()
    {
        $result = array();
        $result['from'] = $this->getData('custom_design_from');
        $result['to'] = $this->getData('custom_design_to');

        return $result;
    }

    /**
     * Retrieve Product URL
     *
     * @param  bool $useSid
     * @return string
     */
    public function getProductUrl($useSid = null)
    {
        return $this->getUrlModel()->getProductUrl($this, $useSid);
    }

    /**
     * Retrieve URL in current store
     *
     * @param array $params the route params
     * @return string
     */
    public function getUrlInStore($params = array())
    {
        return $this->getUrlModel()->getUrlInStore($this, $params);
    }

    /**
     * Formats URL key
     *
     * @param string $str URL
     * @return string
     */
    public function formatUrlKey($str)
    {
        return $this->getUrlModel()->formatUrlKey($str);
    }

    /**
     * Retrieve Product Url Path (include category)
     *
     * @param \Magento\Catalog\Model\Category $category
     * @return string
     */
    public function getUrlPath($category=null)
    {
        return $this->getUrlModel()->getUrlPath($this, $category);
    }

    /**
     * Save current attribute with code $code and assign new value
     *
     * @param string $code  Attribute code
     * @param mixed  $value New attribute value
     * @param int    $store Store ID
     * @return void
     */
    public function addAttributeUpdate($code, $value, $store)
    {
        $oldValue = $this->getData($code);
        $oldStore = $this->getStoreId();

        $this->setData($code, $value);
        $this->setStoreId($store);
        $this->getResource()->saveAttribute($this, $code);

        $this->setData($code, $oldValue);
        $this->setStoreId($oldStore);
    }

    /**
     * Renders the object to array
     *
     * @param array $arrAttributes Attribute array
     * @return array
     */
    public function toArray(array $arrAttributes=array())
    {
        $data = parent::toArray($arrAttributes);
        if ($stock = $this->getStockItem()) {
            $data['stock_item'] = $stock->toArray();
        }
        unset($data['stock_item']['product']);
        return $data;
    }

    /**
     * Same as setData(), but also initiates the stock item (if it is there)
     *
     * @param array $data Array to form the object from
     * @return \Magento\Catalog\Model\Product
     */
    public function fromArray($data)
    {
        if (isset($data['stock_item'])) {
            if ($this->_catalogData->isModuleEnabled('Magento_CatalogInventory')) {
                $stockItem = $this->_stockItemFactory->create()
                    ->setData($data['stock_item'])
                    ->setProduct($this);
                $this->setStockItem($stockItem);
            }
            unset($data['stock_item']);
        }
        $this->setData($data);
        return $this;
    }

    /**
     * Delete product
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function delete()
    {
        parent::delete();
        $this->_eventManager->dispatch($this->_eventPrefix.'_delete_after_done', array($this->_eventObject=>$this));
        return $this;
    }

    /**
     * Returns request path
     *
     * @return string
     */
    public function getRequestPath()
    {
        return $this->_getData('request_path');
    }

    /**
     * Custom function for other modules
     * @return string
     */

    public function getGiftMessageAvailable()
    {
        return $this->_getData('gift_message_available');
    }

    /**
     * Returns rating summary
     *
     * @return mixed
     */
    public function getRatingSummary()
    {
        return $this->_getData('rating_summary');
    }

    /**
     * Check is product composite
     *
     * @return bool
     */
    public function isComposite()
    {
        return $this->getTypeInstance()->isComposite($this);
    }

    /**
     * Check if product can be configured
     *
     * @return bool
     */
    public function canConfigure()
    {
        $options = $this->getOptions();
        return !empty($options) || $this->getTypeInstance()->canConfigure($this);
    }

    /**
     * Retrieve sku through type instance
     *
     * @return string
     */
    public function getSku()
    {
        return $this->getTypeInstance()->getSku($this);
    }

    /**
     * Retrieve weight through type instance
     *
     * @return float
     */
    public function getWeight()
    {
        return $this->getTypeInstance()->getWeight($this);
    }

    /**
     * Retrieve option instance
     *
     * @return Product\Option
     */
    public function getOptionInstance()
    {
        return $this->_optionInstance;
    }

    /**
     * Retrieve options collection of product
     *
     * @return \Magento\Catalog\Model\Resource\Product\Option\Collection
     */
    public function getProductOptionsCollection()
    {
        $collection = $this->getOptionInstance()
            ->getProductOptionCollection($this);

        return $collection;
    }

    /**
     * Add option to array of product options
     *
     * @param Product\Option $option
     * @return \Magento\Catalog\Model\Product
     */
    public function addOption(Product\Option $option)
    {
        $this->_options[$option->getId()] = $option;
        return $this;
    }

    /**
     * Get option from options array of product by given option id
     *
     * @param int $optionId
     * @return Product\Option|null
     */
    public function getOptionById($optionId)
    {
        if (isset($this->_options[$optionId])) {
            return $this->_options[$optionId];
        }

        return null;
    }

    /**
     * Get all options of product
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->_options;
    }

    /**
     * Retrieve is a virtual product
     *
     * @return bool
     */
    public function getIsVirtual()
    {
        return $this->getTypeInstance()->isVirtual($this);
    }

    /**
     * Add custom option information to product
     *
     * @param   string $code    Option code
     * @param   mixed  $value   Value of the option
     * @param   int|Product    $product Product ID
     * @return  $this
     */
    public function addCustomOption($code, $value, $product=null)
    {
        $product = $product ? $product : $this;
        $option = $this->_itemOptionFactory->create()
            ->addData(array(
                'product_id'=> $product->getId(),
                'product'   => $product,
                'code'      => $code,
                'value'     => $value,
            ));
        $this->_customOptions[$code] = $option;
        return $this;
    }

    /**
     * Sets custom options for the product
     *
     * @param array $options Array of options
     * @return void
     */
    public function setCustomOptions(array $options)
    {
        $this->_customOptions = $options;
    }

    /**
     * Get all custom options of the product
     *
     * @return array
     */
    public function getCustomOptions()
    {
        return $this->_customOptions;
    }

    /**
     * Get product custom option info
     *
     * @param   string $code
     * @return  array
     */
    public function getCustomOption($code)
    {
        if (isset($this->_customOptions[$code])) {
            return $this->_customOptions[$code];
        }
        return null;
    }

    /**
     * Checks if there custom option for this product
     *
     * @return bool
     */
    public function hasCustomOptions()
    {
        if (count($this->_customOptions)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check availability display product in category
     *
     * @param   int $categoryId
     * @return  bool
     */
    public function canBeShowInCategory($categoryId)
    {
        return $this->_getResource()->canBeShowInCategory($this, $categoryId);
    }

    /**
     * Retrieve category ids where product is available
     *
     * @return array
     */
    public function getAvailableInCategories()
    {
        return $this->_getResource()->getAvailableInCategories($this);
    }

    /**
     * Retrieve default attribute set id
     *
     * @return int
     */
    public function getDefaultAttributeSetId()
    {
        return $this->getResource()->getEntityType()->getDefaultAttributeSetId();
    }

    /**
     * Return Catalog Product Image helper instance
     *
     * @return \Magento\Catalog\Helper\Image
     */
    protected function _getImageHelper()
    {
        return $this->_catalogImage;
    }

    /**
     * Returns system reserved attribute codes
     *
     * @return array Reserved attribute names
     */
    public function getReservedAttributes()
    {
        if ($this->_reservedAttributes === null) {
            $_reserved = array('position');
            $methods = get_class_methods(__CLASS__);
            foreach ($methods as $method) {
                if (preg_match('/^get([A-Z]{1}.+)/', $method, $matches)) {
                    $method = $matches[1];
                    $tmp = strtolower(preg_replace('/(.)([A-Z])/', "$1_$2", $method));
                    $_reserved[] = $tmp;
                }
            }
            $_allowed = array(
                'type_id','calculated_final_price','request_path','rating_summary'
            );
            $this->_reservedAttributes = array_diff($_reserved, $_allowed);
        }
        return $this->_reservedAttributes;
    }

    /**
     * Check whether attribute reserved or not
     *
     * @param \Magento\Catalog\Model\Entity\Attribute $attribute Attribute model object
     * @return boolean
     */
    public function isReservedAttribute ($attribute)
    {
        return $attribute->getIsUserDefined()
            && in_array($attribute->getAttributeCode(), $this->getReservedAttributes());
    }

    /**
     * Set original loaded data if needed
     *
     * @param string $key
     * @param mixed $data
     * @return \Magento\Object
     */
    public function setOrigData($key=null, $data=null)
    {
        if ($this->_appState->getAreaCode() === \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE) {
            return parent::setOrigData($key, $data);
        }

        return $this;
    }

    /**
     * Reset all model data
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function reset()
    {
        $this->unlockAttributes();
        $this->_clearData();
        return $this;
    }

    /**
     * Get cache tags associated with object id
     *
     * @return string[]
     */
    public function getCacheIdTags()
    {
        $tags = parent::getCacheIdTags();
        $affectedCategoryIds = $this->getAffectedCategoryIds();
        if (!$affectedCategoryIds) {
            $affectedCategoryIds = $this->getCategoryIds();
        }
        foreach ($affectedCategoryIds as $categoryId) {
            $tags[] = \Magento\Catalog\Model\Category::CACHE_TAG.'_'.$categoryId;
        }
        return $tags;
    }

    /**
     * Check for empty SKU on each product
     *
     * @param  array $productIds
     * @return boolean|null
     */
    public function isProductsHasSku(array $productIds)
    {
        $products = $this->_getResource()->getProductsSku($productIds);
        if (count($products)) {
            foreach ($products as $product) {
                if (!strlen($product['sku'])) {
                    return false;
                }
            }
            return true;
        }
        return null;
    }

    /**
     * Parse buyRequest into options values used by product
     *
     * @param  \Magento\Object $buyRequest
     * @return \Magento\Object
     */
    public function processBuyRequest(\Magento\Object $buyRequest)
    {
        $options = new \Magento\Object();

        /* add product custom options data */
        $customOptions = $buyRequest->getOptions();
        if (is_array($customOptions)) {
            array_filter($customOptions, function ($value) {
                return $value !== '';
            });
            $options->setOptions($customOptions);
        }

        /* add product type selected options data */
        $type = $this->getTypeInstance();
        $typeSpecificOptions = $type->processBuyRequest($this, $buyRequest);
        $options->addData($typeSpecificOptions);

        /* check correctness of product's options */
        $options->setErrors($type->checkProductConfiguration($this, $buyRequest));

        return $options;
    }

    /**
     * Get preconfigured values from product
     *
     * @return \Magento\Object
     */
    public function getPreconfiguredValues()
    {
        $preConfiguredValues = $this->getData('preconfigured_values');
        if (!$preConfiguredValues) {
            $preConfiguredValues = new \Magento\Object();
        }

        return $preConfiguredValues;
    }

    /**
     * Prepare product custom options.
     * To be sure that all product custom options does not has ID and has product instance
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function prepareCustomOptions()
    {
        foreach ($this->getCustomOptions() as $option) {
            if (!is_object($option->getProduct()) || $option->getId()) {
                $this->addCustomOption($option->getCode(), $option->getValue());
            }
        }

        return $this;
    }

    /**
     * Clearing references on product
     *
     * @return \Magento\Catalog\Model\Product
     */
    protected function _clearReferences()
    {
        $this->_clearOptionReferences();
        return $this;
    }

    /**
     * Clearing product's data
     *
     * @return \Magento\Catalog\Model\Product
     */
    protected function _clearData()
    {
        foreach ($this->_data as $data) {
            if (is_object($data) && method_exists($data, 'reset')) {
                $data->reset();
            }
        }

        $this->setData(array());
        $this->setOrigData();
        $this->_customOptions       = array();
        $this->_options             = array();
        $this->_canAffectOptions    = false;
        $this->_errors              = array();

        return $this;
    }

    /**
     * Clearing references to product from product's options
     *
     * @return \Magento\Catalog\Model\Product
     */
    protected function _clearOptionReferences()
    {
        /**
         * unload product options
         */
        if (!empty($this->_options)) {
            foreach ($this->_options as $key => $option) {
                $option->setProduct();
                $option->clearInstance();
            }
        }

        return $this;
    }

    /**
     * Retrieve product entities info as array
     *
     * @param string|array $columns One or several columns
     * @return array
     */
    public function getProductEntitiesInfo($columns = null)
    {
        return $this->_getResource()->getProductEntitiesInfo($columns);
    }

    /**
     * Checks whether product has disabled status
     *
     * @return bool
     */
    public function isDisabled()
    {
        return $this->getStatus() == \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED;
    }

    /**
     * Sets product image from it's child if possible
     *
     * @return string
     */
    public function getImage()
    {
        $this->getTypeInstance()->setImageFromChildProduct($this);
        return parent::getImage();
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return array(self::CACHE_TAG  . '_' . $this->getId());
    }
}
