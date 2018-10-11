<?php

namespace Breadlesscode\Listable\Fusion;

use Neos\Flow\Annotations as Flow;
use Neos\Fusion\FusionObjects\AbstractFusionObject;
use Neos\Fusion\Core\Runtime;

class PaginationArrayImplementation extends AbstractFusionObject
{
    protected $currentPage;
    protected $itemsPerPage;
    protected $maximumNumberOfLinks;
    protected $totalCount;
    protected $numberOfPages;
    protected $firstPage;
    protected $lastPage;
    /**
     * @inheritDoc
     */
    public function __construct(Runtime $runtime, $path, $fusionObjectName)
    {
        parent::__construct($runtime, $path, $fusionObjectName);

        $this->currentPage = \intval($this->fusionValue('currentPage'));
        $this->itemsPerPage = \intval($this->fusionValue('itemsPerPage'));
        $this->maximumNumberOfLinks = \intval($this->fusionValue('maximumNumberOfLinks'));
        $this->totalCount = \intval($this->fusionValue('totalCount'));
        $this->paginationConfig = $this->fusionValue('paginationConfig');
        $this->numberOfPages = $this->getNumberOfPages();

        $this->calculateFirstAndLastPage();
    }
    /**
     * calculates the first and last page to show
     *
     * @return void
     */
    protected function calculateFirstAndLastPage()
    {
        $delta = \floor($this->maximumNumberOfLinks / 2);
        $firstPage = $this->currentPage - $delta;
        $lastPage = $this->currentPage + $delta + ($this->maximumNumberOfLinks % 2 === 0 ? 1 : 0);

        if ($firstPage < 1) {
            $lastPage -= $firstPage - 1;
        }
        if ($lastPage > $this->numberOfPages) {
            $firstPage -= ($lastPage - $this->numberOfPages);
        }

        $this->firstPage = \max($firstPage, 1);
        $this->lastPage = \min($lastPage, $this->numberOfPages);
    }
    /**
     * calculates the number of pages
     *
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
     *
     * @return array
     */
    protected function getPageArray()
    {
        $range = \range($this->firstPage, $this->lastPage);
        $pageArray = [];

        foreach ($range as $page) {
            $pageArray[] = [
                'page' => $page,
                'label' => $page,
                'type' => 'page'
            ];
        }

        return $pageArray;
    }
    /**
     * add a item to the start of the page array
     *
     * @param array $pageArray
     * @param integer $page
     * @param string $type
     * @return array
     */
    protected function addItemToTheStartOfPageArray($pageArray, $page, $type)
    {
        array_unshift($pageArray, [
            'page' => $page,
            'label' => $this->paginationConfig['labels'][$type],
            'type' => $type
        ]);

        return $pageArray;
    }
    /**
     * add a item to the end of the page array
     *
     * @param array $pageArray
     * @param integer $page
     * @param string $type
     * @return array
     */
    protected function addItemToTheEndOfPageArray($pageArray, $page, $type)
    {
        $pageArray[] = [
            'page' => $page,
            'label' => $this->paginationConfig['labels'][$type],
            'type' => $type
        ];

        return $pageArray;
    }
    /**
     * @return array
     */
    public function evaluate()
    {
        if ($this->totalCount > 0 !== true || $this->numberOfPages === 1) {
            return [];
        }

        $pageArray = $this->getPageArray();
        // add seperators
        if ($this->paginationConfig['showSeperators']) {
            if ($this->firstPage > 1) {
                $pageArray = $this->addItemToTheStartOfPageArray($pageArray, false, 'seperator');
            }
            if ($this->lastPage  < $this->numberOfPages) {
                $pageArray = $this->addItemToTheEndOfPageArray($pageArray, false, 'seperator');
            }
        }
        // add previous and next
        if ($this->paginationConfig['showNextAndPrevious']) {
            if ($this->firstPage > 1 || $this->paginationConfig['alwaysShowNextAndPrevious']) {
                $pageArray = $this->addItemToTheStartOfPageArray($pageArray, $this->currentPage - 1, 'previous');
            }
            if ($this->lastPage  < $this->numberOfPages || $this->paginationConfig['alwaysShowNextAndPrevious']) {
                if($this->currentPage < $this->numberOfPages) {
                    $pageArray = $this->addItemToTheEndOfPageArray($pageArray, $this->currentPage + 1, 'next');
                } else {
                    $pageArray = $this->addItemToTheEndOfPageArray($pageArray, $this->currentPage, 'next');
                }
            }
        }
        if ($this->paginationConfig['showFirstAndLast']) {
            if ($this->firstPage > 1 || $this->paginationConfig['alwaysShowFirstAndLast']) {
                $pageArray = $this->addItemToTheStartOfPageArray($pageArray, 1, 'first');
            }
            if ($this->lastPage  < $this->numberOfPages || $this->paginationConfig['alwaysShowFirstAndLast']) {
                $pageArray = $this->addItemToTheEndOfPageArray($pageArray, $this->numberOfPages, 'last');
            }
        }
        return $pageArray;
    }
}
