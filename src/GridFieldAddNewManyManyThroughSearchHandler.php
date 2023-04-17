<?php

namespace Fromholdio\GridFieldAddNewManyManyThrough;

use SilverStripe\Control\Controller;
use SilverStripe\Control\RequestHandler;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\PaginatedList;
use SilverStripe\View\ArrayData;

class GridFieldAddNewManyManyThroughSearchHandler extends RequestHandler
{
    protected $button;
    protected $gridField;
    protected $items;

    private static $allowed_actions = [
        'index',
        'add',
        'SearchForm'
    ];

    public function __construct(GridField $gridField, GridFieldAddNewManyManyThroughSearchButton $button)
    {
        $this->setGridField($gridField);
        $this->setButton($button);
        parent::__construct();
    }

    public function index()
    {
        return $this->renderWith(__CLASS__);
    }

    public function add($request)
    {
        $id = $request->postVar('id');
        if (!$id) {
            $this->httpError(400);
        }

        $searchList = $this->getSearchList();
        $searchItem = $searchList->find('ID', $id);
        if (!$searchItem || !$searchItem->exists()) {
            $this->httpError(400);
        }

        $joinList = $this->getJoinList();
        $joinList->add($searchItem);

//        $gridList = $this->getGridList();
//        $gridDataClass = $gridList->dataClass();
//        $joinKey = $this->getJoinKey();
//
//        $newItem = $gridDataClass::create();
//        $newItem->{$joinKey} = $id;
//
//        $extraData = $this->getButton()->getExtraData();
//        if (is_array($extraData) && count($extraData) > 0) {
//            foreach ($extraData as $key => $value) {
//                $newItem->{$key} = $value;
//            }
//        }
//
//        $newItem->write();
//        $gridList->add($newItem);
    }

    public function SearchForm()
    {
        $form = Form::create(
            $this,
            'SearchForm',
            FieldList::create(
                TextField::create('SearchTerm', 'Search')
            ),
            FieldList::create(
                FormAction::create('doSearch', 'Search')
                    ->setUseButtonTag(true)
                    ->addExtraClass('btn btn-primary font-icon-search')
            )
        );
        $form->addExtraClass('stacked add-new-manymanythrough-search-form form--no-dividers');
        $form->setFormMethod('GET');
        return $form;
    }

    public function doSearch($data, $form)
    {
        $searchList = $this->getSearchList();
        $searchFields = $this->getButton()->getSearchFields();
        if (empty($searchFields)) {
            $searchFields = ['Title'];
        }

        if (isset($data['SearchTerm']) && count($searchFields) > 0) {
            $searchTerm = $data['SearchTerm'];
            $filterAny = [];
            foreach ($searchFields as $searchField) {
                $filterName = $searchField;
                if (strpos($searchField, ':') === false) {
                    $filterName .= ':PartialMatch';
                }
                $filterAny[$filterName] = $searchTerm;
            }
            $results = $searchList->filterAny($filterAny);
        } else {
            $results = $searchList;
        }

        $this->items = $results;
        $data = $this->customise(['SearchForm' => $form]);
        return $data->index();
    }

    public function Items()
    {
        $searchList = $this->items;
        if (is_null($searchList)) {
            $searchList = $this->getSearchList();
        }
        $format = $this->getItemFormat();
        $items = ArrayList::create();
        foreach ($searchList as $searchItem) {
            $items->push(ArrayData::create(['Result' => $searchItem, 'Title' => $searchItem->{$format}]));
        }
        return PaginatedList::create($items, $this->getRequest());
    }

    public function Link($action = null)
    {
        return Controller::join_links(
            $this->getGridField()->Link(),
            $this->getButton()->getURLSegment(),
            $action
        );
    }

    protected function getJoinKey()
    {
        return $this->getButton()->getJoinKey();
    }

    protected function getJoinList()
    {
        return $this->getButton()->getJoinList();
    }

    protected function getGridList()
    {
        return $this->getGridField()->getList();
    }

    protected function getItemFormat()
    {
        return $this->getButton()->getResultFormat();
    }

    protected function getSearchList()
    {
        $excludeGridList = !$this->getButton()->getDoAllowDuplicate();
        $searchList = $this->getButton()->getSearchList();
        $gridList = $this->getGridList();
        if ($excludeGridList) {
            $gridListIDs = $gridList->columnUnique('ID');
            if (count($gridListIDs) > 0) {
                $searchList = $searchList->exclude('ID', $gridListIDs);
            }
        }
        return $searchList;
    }

    public function setGridField($gridField)
    {
        $this->gridField = $gridField;
        return $this;
    }

    public function getGridField()
    {
        return $this->gridField;
    }

    public function setButton($button)
    {
        $this->button = $button;
        return $this;
    }

    public function getButton() :GridFieldAddNewManyManyThroughSearchButton
    {
        return $this->button;
    }
}
