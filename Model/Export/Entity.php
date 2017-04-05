<?php
/**
 * Faonni
 *  
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade module to newer
 * versions in the future.
 * 
 * @package     Faonni_CategoryImportExport
 * @copyright   Copyright (c) 2017 Karliuka Vitalii(karliuka.vitalii@gmail.com) 
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Faonni\CategoryImportExport\Model\Export;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\ImportExport\Model\Export\Entity\AbstractEav;
use Magento\ImportExport\Model\Export\Factory;
use Magento\ImportExport\Model\ResourceModel\CollectionByPagesIteratorFactory;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Eav\Model\Config;

/**
 * Export category entity model
 */
class Entity extends AbstractEav
{
    /**
     * Attribute collection name
     */
    const ATTRIBUTE_COLLECTION_NAME = 'Magento\Catalog\Model\ResourceModel\Category\Attribute\Collection';
	
	/**
     * Categories whose data is exported
     *
     * @var \Magento\Catalog\Model\ResourceModel\Category\Collection
     */
    protected $_categoryCollection;
	
    /**
	 * Initialize export model
	 *
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param Factory $factory
     * @param CollectionByPagesIteratorFactory $resourceColFactory
     * @param TimezoneInterface $localeDate
     * @param Config $eavConfig
     * @param CollectionFactory $collectionFactory
     * @param array $data
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        Factory $factory,
        CollectionByPagesIteratorFactory $resourceColFactory,
        TimezoneInterface $localeDate,
        Config $eavConfig,
        CollectionFactory $collectionFactory,
        array $data = []
    ) {
        parent::__construct(
            $scopeConfig,
            $storeManager,
            $factory,
            $resourceColFactory,
            $localeDate,
            $eavConfig,
            $data
        );

        $this->_categoryCollection = isset($data['category_collection']) 
			? $data['category_collection'] 
			: $collectionFactory->create();

        $this->_initAttributeValues()
			->_initStores()
			->_initWebsites(true);
    }
	
    /**
     * Export process
     *
     * @return string
     */
    public function export()
	{
        $this->_prepareEntityCollection($this->_getEntityCollection());
        $writer = $this->getWriter();

        // create export file
        $writer->setHeaderCols($this->_getHeaderColumns());
        $this->_exportCollectionByPages($this->_getEntityCollection());

        return $writer->getContents();		
	}

    /**
     * Export one item
     *
     * @param \Magento\Framework\Model\AbstractModel $item
     * @return void
     */
    public function exportItem($item)
	{
        $row = $this->_addAttributeValuesToRow($item);
        $this->getWriter()->writeRow($row);		
	}
	
    /**
     * Entity type code getter
     *
     * @abstract
     * @return string
     */
    public function getEntityTypeCode()
	{
		return 'catalog_category';
	}

    /**
     * Get header columns
     *
     * @return array
     */
    protected function _getHeaderColumns()
	{
		return $this->_getExportAttributeCodes();
	}

    /**
     * Get entity collection
     *
     * @param bool $resetCollection
     * @return \Magento\Framework\Data\Collection\AbstractDb
     */
    protected function _getEntityCollection($resetCollection = false)
	{
		return $this->_categoryCollection;
	}	
}