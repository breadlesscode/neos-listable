<?php

namespace Breadlesscode\Listable\Fusion;

use Neos\Fusion\FusionObjects\AbstractFusionObject;
use Neos\Fusion\Core\Runtime;

class PaginationArrayImplementation extends AbstractFusionObject
{
    /**
     * @var int
     */
    protected $currentPage;
    /**
     * @var int
     */
    protected $itemsPerPage;
    /**
     * @var int
     */
    protected $maximumNumberOfLinks;
    /**
     * @var int
     */
    protected $itemCount;
    /**
     * @var array
     */
    protected $paginationConfig;
    /**
     * @var int
     */
    protected $numberOfPages;
    /**
     * @var int
     */
    protected $firstPageShown;
    /**
     * @var int
     */
    protected $lastPageShown;
    /**
     * @var array
     */
    protected $pages = [];

    /**
     * @inheritDoc
     */
    public function __construct(Runtime $runtime, $path, $fusionObjectName)
    {
        parent::__construct($runtime, $path, $fusionObjectName);

        $this->currentPage = (int) $this->fusionValue('currentPage');
        $this->itemsPerPage = (int) $this->fusionValue('itemsPerPage');
        $this->itemCount = (int) $this->fusionValue('totalCount');
        $this->maximumNumberOfLinks = (int) $this->fusionValue('maximumNumberOfLinks');
        $this->paginationConfig = $this->fusionValue('paginationConfig');

        $this->numberOfPages = \ceil($this->itemCount / $this->itemsPerPage);

        $this->calculateFirstAndLastPage();
        $this->createPageArray();
    }

    /**
     * calculates the first and last page to show
     *
     * @return void
     */
    protected function calculateFirstAndLastPage()
    {
        $delta = \floor($this->maximumNumberOfLinks / 2);
        $firstPageShown = $this->currentPage - $delta;
        $lastPageShown = $this->currentPage + $delta;
        // check for the correct number of shown links for the left side
        // if you are on the first page the number of missing links should be added to the right links
        if ($firstPageShown < 1) {
            $lastPageShown -= $firstPageShown - 1;
        }
        // check for the correct number of shown links for the right side
        // if you are on the last page the number of missing links should be added to the left links
        if ($lastPageShown > $this->numberOfPages) {
            $firstPageShown -= ($lastPageShown - $this->numberOfPages);
        }
        // make sure the calculdated numbers are not out of range
        $this->firstPageShown = \max($firstPageShown, 1);
        $this->lastPageShown = \min($lastPageShown, $this->numberOfPages);
    }

    /**
     * get an array of pages to display
     */
    protected function createPageArray()
    {
        $range = \range($this->firstPageShown, $this->lastPageShown);

        foreach ($range as $page) {
            $this->pages[] = [
                'page' => $page,
                'label' => $page,
                'type' => 'page'
            ];
        }
    }

    /**
     * add a item to the start of the page array
     *
     * @param integer $page
     * @param $label
     * @param string $type
     */
    protected function addItemToTheStartOfPageArray($page, $label, $type)
    {
        array_unshift($this->pages, [
            'page' => $page,
            'label' => $label,
            'type' => $type
        ]);
    }

    /**
     * add a item to the end of the page array
     *
     * @param $page
     * @param $label
     * @param $type
     */
    protected function addItemToTheEndOfPageArray($page, $label, $type)
    {
        $this->pages[] = [
            'page' => $page,
            'label' => $label,
            'type' => $type
        ];
    }

    /**
     * @return array
     */
    public function evaluate()
    {
        if ($this->itemCount > 0 !== true || $this->numberOfPages === 1) {
            return [];
        }
        // add configured seperators
        if ($this->paginationConfig['showSeperators']) {
            if ($this->firstPageShown > 1) {
                $this->addItemToTheStartOfPageArray(false, $this->paginationConfig['labels']['seperator'], 'seperator');
            }
            if ($this->lastPageShown  < $this->numberOfPages) {
                $this->addItemToTheEndOfPageArray(false, $this->paginationConfig['labels']['seperator'], 'seperator');
            }
        }
        // add numeric first & last links
        if ($this->paginationConfig['showFirstAndLastNumeric']) {
            if ($this->firstPageShown > 1 || $this->paginationConfig['alwaysShowFirstAndLast']) {
                $this->addItemToTheStartOfPageArray(1, 1, 'first');
            }
            if ($this->lastPageShown  < $this->numberOfPages || $this->paginationConfig['alwaysShowFirstAndLast']) {
                $this->addItemToTheEndOfPageArray($this->numberOfPages, $this->numberOfPages, 'last');
            }
        }
        // add previous and next
        if ($this->paginationConfig['showNextAndPrevious']) {
            if ($this->currentPage > 1 || $this->paginationConfig['alwaysShowNextAndPrevious']) {
                if ($this->currentPage > 1) {
                    $this->addItemToTheStartOfPageArray($this->currentPage - 1, $this->paginationConfig['labels']['previous'],'previous');
                } else {
                    $this->addItemToTheStartOfPageArray(false, $this->paginationConfig['labels']['previous'],'previous');
                }
            }
            if ($this->currentPage < $this->lastPageShown || $this->paginationConfig['alwaysShowNextAndPrevious']) {
                if ($this->currentPage < $this->numberOfPages) {
                    $this->addItemToTheEndOfPageArray($this->currentPage + 1, $this->paginationConfig['labels']['next'],  'next');
                } else {
                    $this->addItemToTheEndOfPageArray(false, $this->paginationConfig['labels']['next'],  'next');
                }
            }
        }
        // add first & last link with configured label
        if ($this->paginationConfig['showFirstAndLast']) {
            if ($this->firstPageShown > 1 || $this->paginationConfig['alwaysShowFirstAndLast']) {
                $this->addItemToTheStartOfPageArray(1, $this->paginationConfig['labels']['first'], 'first');
            }
            if ($this->lastPageShown  < $this->numberOfPages || $this->paginationConfig['alwaysShowFirstAndLast']) {
                $this->addItemToTheEndOfPageArray($this->numberOfPages, $this->paginationConfig['labels']['last'], 'last');
            }
        }

        return $this->pages;
    }
}
