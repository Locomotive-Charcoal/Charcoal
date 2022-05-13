<?php

namespace Charcoal\Object;

use Exception;
use InvalidArgumentException;

/**
 *
 */
trait CategoryTrait
{
    /**
     * @var string
     */
    private $categoryItemType;

    /**
     * @var \Charcoal\Object\CategorizableInterface[]|array
     */
    private $categoryItems;

    /**
     * @var integer
     */
    protected $numCategoryItems;

    /**
     * @param string $type The category item type.
     * @throws InvalidArgumentException If the type argument is not a string.
     * @return self
     */
    public function setCategoryItemType($type)
    {
        if (!is_string($type)) {
            throw new InvalidArgumentException(
                'Item type must be a string.'
            );
        }
        $this->categoryItemType = $type;
        return $this;
    }

    /**
     * @throws Exception If no item type was previously set.
     * @return string
     */
    public function getCategoryItemType()
    {
        if ($this->categoryItemType === null) {
            throw new Exception(
                'Item type is unset. Set item type before calling getter.'
            );
        }
        return $this->categoryItemType;
    }

    /**
     * Alias of {@see self::getCategoryItemType()}.
     *
     * @return string
     */
    public function categoryItemType()
    {
        return $this->getCategoryItemType();
    }

    /**
     * Gets the number of items, directly within this category.
     *
     * @return integer
     */
    public function getNumCategoryItems()
    {
        if ($this->numCategoryItems === null) {
            $items = $this->getCategoryItems();
            if (is_array($items) || ($items instanceof \Countable)) {
                $count = count($items);
            } else {
                $count = 0;
            }

            $this->numCategoryItems = $count;
        }

        return $this->numCategoryItems;
    }

    /**
     * Alias of {@see self::getNumCategoryItems()}.
     *
     * @return integer
     */
    public function numCategoryItems()
    {
        return $this->getNumCategoryItems();
    }

    /**
     * Gets wether the category has any items, directly within it.
     *
     * @return boolean
     */
    public function hasCategoryItems()
    {
        $numItems = $this->getNumCategoryItems();
        return ($numItems > 0);
    }

    /**
     * Retrieves the category items, directly within it.
     *
     * @return \Charcoal\Object\CategorizableInterface[]|array A list of `CategorizableInterface` objects
     */
    public function getCategoryItems()
    {
        if ($this->categoryItems === null) {
            $this->categoryItems    = $this->loadCategoryItems();
            $this->numCategoryItems = null;
        }
        return $this->categoryItems;
    }

    /**
     * Loads the category items (directly within it).
     *
     * This method is abstract so must be reimplemented.
     * Typically, a class would use a CollectionLoader to load the category items.
     *
     * @return \Charcoal\Object\CategorizableInterface[]|array
     */
    abstract protected function loadCategoryItems();
}
