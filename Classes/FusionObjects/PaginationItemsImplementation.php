<?php

namespace Breadlesscode\Listable\FusionObjects;

use Neos\Fusion\FusionObjects\AbstractFusionObject;
use Neos\Fusion\Core\Runtime;

class PaginationItemsImplementation extends AbstractFusionObject
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
    protected $config;
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
        $this->itemCount = (int) $this->fusionValue('itemCount');
        $this->config = $this->fusionValue('config');
        $this->maximumNumberOfLinks = (int) $this->config['numberOfLinks'];
        $this->numberOfPages = (int) \ceil($this->itemCount / $this->itemsPerPage);

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
        // make sure the calculated numbers are not out of range
        $this->firstPageShown = \max($firstPageShown, 1);
        $this->lastPageShown = \min($lastPageShown, $this->numberOfPages);
    }

    /**
     * @param int $page
     * @return string
     */
    protected function isCurrentPage(int $page) : string
    {
        return $this->currentPage === $page;
    }

    /**
     * get an array of pages to display
     */
    protected function createPageArray()
    {
        $range = \range($this->firstPageShown, $this->lastPageShown);

        foreach ($range as $page) {
            $this->pages[] = [
                'page' => (int) $page,
                'label' => (string) $page,
                'type' => 'page',
                'isCurrent' => $this->isCurrentPage($page),
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
    protected function addItemToTheStartOfPageArray(int $page, string $label, string $type)
    {
        array_unshift($this->pages, [
            'page' => $page,
            'label' => $label,
            'type' => $type,
            'isCurrent' => $this->isCurrentPage($page),
        ]);
    }

    /**
     * add a item to the end of the page array
     *
     * @param $page
     * @param $label
     * @param $type
     */
    protected function addItemToTheEndOfPageArray(int $page, string $label, string $type)
    {
        $this->pages[] = [
            'page' => $page,
            'label' => $label,
            'type' => $type,
            'isCurrent' => $this->isCurrentPage($page),
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
        if ($this->config['showSeparators']) {
            if ($this->firstPageShown > 1) {
                $this->addItemToTheStartOfPageArray(false, $this->config['labels']['separator'], 'separator');
            }
            if ($this->lastPageShown  < $this->numberOfPages) {
                $this->addItemToTheEndOfPageArray(false, $this->config['labels']['separator'], 'separator');
            }
        }
        // add numeric first & last links
        if ($this->config['showFirstAndLastNumeric']) {
            if ($this->firstPageShown > 1 || $this->config['alwaysShowFirstAndLast']) {
                $this->addItemToTheStartOfPageArray(1, 1, 'first');
            }
            if ($this->lastPageShown  < $this->numberOfPages || $this->config['alwaysShowFirstAndLast']) {
                $this->addItemToTheEndOfPageArray($this->numberOfPages, $this->numberOfPages, 'last');
            }
        }
        // add previous and next
        if ($this->config['showNextAndPrevious']) {
            if ($this->currentPage > 1 || $this->config['alwaysShowNextAndPrevious']) {
                if ($this->currentPage > 1) {
                    $this->addItemToTheStartOfPageArray($this->currentPage - 1, $this->config['labels']['previous'],'previous');
                } else {
                    $this->addItemToTheStartOfPageArray(false, $this->config['labels']['previous'], 'previous');
                }
            }
            if ($this->currentPage < $this->lastPageShown || $this->config['alwaysShowNextAndPrevious']) {
                if ($this->currentPage < $this->numberOfPages) {
                    $this->addItemToTheEndOfPageArray($this->currentPage + 1, $this->config['labels']['next'],  'next');
                } else {
                    $this->addItemToTheEndOfPageArray(false, $this->config['labels']['next'], 'next');
                }
            }
        }
        // add first & last link with configured label
        if ($this->config['showFirstAndLast']) {
            if ($this->firstPageShown > 1 || $this->config['alwaysShowFirstAndLast']) {
                $this->addItemToTheStartOfPageArray(1, $this->config['labels']['first'], 'first');
            }
            if ($this->lastPageShown  < $this->numberOfPages || $this->config['alwaysShowFirstAndLast']) {
                $this->addItemToTheEndOfPageArray($this->numberOfPages, $this->config['labels']['last'], 'last');
            }
        }

        return $this->pages;
    }
}
