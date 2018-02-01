<?php

namespace Breadlesscode\Listable\Fusion;

use Neos\Flow\Annotations as Flow;
use Neos\Fusion\FusionObjects\AbstractFusionObject;

class PaginationArrayImplementation extends AbstractFusionObject
{
    protected $currentPage;
    protected $itemsPerPage;
    protected $maximumNumberOfLinks;
    protected $totalCount;
    /**
     * @inheritDoc
     */
    public function __construct(Runtime $runtime, $path, $fusionObjectName)
    {
        parent::__construct($runtime, $path, $fusionObjectName);
        $this->currentPage = \intval($this->fusionValue('currentPage'));
        $this->itemsPerPage = \intval($this->fusionValue('itemsPerPage'));
        $this->maximumNumberOfLinks = \intval($this->fusionValue('maximumNumberOfLinks')) - 2;
        $this->totalCount = \intval($this->fusionValue('totalCount'));
    }
    /**
     * calculates the number of pages
     * @return integer
     */
    protected function getNumberOfPages()
    {
        $numberOfPages = \ceil($this->totalCount / $this->itemsPerPage);
        if ($this->maximumNumberOfLinks > $numberOfPages) {
            return $numberOfPages;
        }
        return $numberOfPages;
    }
    /**
     * get an array of pages to display
     * @return array
     */
    protected function getPageRangeArray($numberOfPages)
    {
        $delta = \floor($this->maximumNumberOfLinks / 2);
        $rangeStart = $this->currentPage - $delta;
        $rangeEnd = $this->currentPage + $delta + ($this->maximumNumberOfLinks % 2 === 0 ? 1 : 0);
        if ($rangeStart < 1) {
            $rangeEnd -= $rangeStart - 1;
        }
        if ($rangeEnd > $numberOfPages) {
            $rangeStart -= ($rangeEnd - $numberOfPages);
        }
        $rangeStart = \max($rangeStart, 1);
        $rangeEnd = \min($rangeEnd, $numberOfPages);
        return \range($rangeStart, $rangeEnd);
    }
    /**
     * @return Array
     */
    public function evaluate()
    {
        if ($this->totalCount > 0 !== true) {
            return [];
        }
        $numberOfPages = $this->getNumberOfPages();
        $pageArray = $this->getPageRangeArray($numberOfPages);
        if ($pageArray[0] > 2) {
            array_unshift($pageArray, "...");
            array_unshift($pageArray, 1);
        }
        if (end($pagesArray) + 1 < $numberOfPages) {
            $links[] = "...";
            $links[] = $numberOfPages;
        }
        return $links;
    }
}
