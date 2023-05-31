<?php

namespace Fromholdio\GridFieldAddNewManyManyThrough;

use SilverStripe\Forms\GridField\GridField_HTMLProvider;
use SilverStripe\Forms\GridField\GridField_URLHandler;
use SilverStripe\ORM\ManyManyThroughList;
use SilverStripe\ORM\ManyManyThroughQueryManipulator;
use SilverStripe\ORM\SS_List;
use SilverStripe\ORM\UnsavedRelationList;
use SilverStripe\View\ArrayData;
use SilverStripe\View\Requirements;

class GridFieldAddNewManyManyThroughSearchButton implements GridField_HTMLProvider, GridField_URLHandler
{
    protected string $buttonClass;
    protected ?string $buttonName;
    protected ?array $extraData;
    protected bool $doAllowDuplicate;
    protected string $fragment;
    protected string $joinKey;
    protected string $title;
    protected string $resultFormat;
    protected array $searchFields;
    protected SS_List $searchList;
    protected ?array $gridFieldReloadList;
    protected ManyManyThroughList|UnsavedRelationList $joinList;

    private static $allowed_actions = [
        'handleSearch'
    ];

    public function __construct(
        ManyManyThroughList|UnsavedRelationList $joinList,
        SS_List $searchList,
        array $searchFields = ['Title'],
        string $resultFormat = 'Title',
        array $extraData = null,
        bool $doAllowDuplicate = false,
        $fragment = 'buttons-before-left',
        string $buttonClass = 'btn-outline-primary',
        string $buttonName = null
    ) {
        $this->setJoinList($joinList);
        $this->setButtonClass($buttonClass);
        $this->setButtonName($buttonName);
        $this->setExtraData($extraData);
        $this->setDoAllowDuplicate($doAllowDuplicate);
        $this->setFragment($fragment);
        $this->setResultFormat($resultFormat);
        $this->setSearchFields($searchFields);
        $this->setSearchList($searchList);
        $this->setTitle(_t('GridFieldExtensions.ADDEXISTING', 'Add Existing'));
        $this->setGridFieldReloadList(null);
    }

    public function setButtonName(?string $buttonName) :self
    {
        $this->buttonName = $buttonName;
        return $this;
    }

    public function getButtonName() :?string
    {
        return $this->buttonName;
    }

    public function setExtraData(?array $extraData) :self
    {
        $this->extraData = $extraData;
        return $this;
    }

    public function getExtraData() :?array
    {
        return $this->extraData;
    }

    public function setGridFieldReloadList(?array $list): self
    {
        $this->gridFieldReloadList = $list;
        return $this;
    }

    public function getGridFieldReloadList(): ?array
    {
        return $this->gridFieldReloadList;
    }

    public function getGridFieldReloadAttribute(): ?string
    {
        $attr = null;
        $list = $this->getGridFieldReloadList();
        if (!empty($list)) {
            $attrData = array_values($list);
            $attr = json_encode($attrData, JSON_FORCE_OBJECT);
        }
        return $attr;
    }

    public function setDoAllowDuplicate(bool $doAllowDuplicate) :self
    {
        $this->doAllowDuplicate = $doAllowDuplicate;
        return $this;
    }

    public function getDoAllowDuplicate() :bool
    {
        return $this->doAllowDuplicate;
    }

    public function setFragment(string $fragment) :self
    {
        $this->fragment = $fragment;
        return $this;
    }

    public function getFragment() :string
    {
        return $this->fragment;
    }

    public function setJoinList(ManyManyThroughList|UnsavedRelationList $list): self
    {
        $this->joinList = $list;
        return $this;
    }

    public function getJoinList(): ManyManyThroughList|UnsavedRelationList
    {
        return $this->joinList;
    }

    public function getJoinKey() :string
    {
        $list = $this->getJoinList();
        $manyManyManipulator = null;
        foreach ($list->dataQuery()->getDataQueryManipulators() as $manipulator) {
            if ($manipulator instanceof ManyManyThroughQueryManipulator) {
                $manyManyManipulator = $manipulator;
                break;
            }
        }
        if (!$manyManyManipulator) {
            throw new \LogicException('No ManyManyThroughQueryManipulator found');
        }
        return $manyManyManipulator->getLocalKey();
    }

    public function setResultFormat(string $resultFormat) :self
    {
        $this->resultFormat = $resultFormat;
        return $this;
    }

    public function getResultFormat() :string
    {
        return $this->resultFormat;
    }

    public function setSearchFields(array $fields) :self
    {
        $this->searchFields = $fields;
        return $this;
    }

    public function getSearchFields()
    {
        return $this->searchFields;
    }

    public function setSearchList(SS_List $searchList) :self
    {
        $this->searchList = $searchList;
        return $this;
    }

    public function getSearchList() :SS_List
    {
        return $this->searchList;
    }

    public function setTitle(string $title) :self
    {
        $this->title = $title;
        return $this;
    }

    public function getTitle() :string
    {
        return $this->title;
    }

    public function setButtonClass(string $class) :self
    {
        $this->buttonClass = $class;
        return $this;
    }

    public function getButtonClass() :string
    {
        return $this->buttonClass;
    }

    public function getHTMLFragments($gridField)
    {
        Requirements::css('fromholdio/silverstripe-gridfield-addnew-manymanythrough: client/css/gridfieldaddnewmanymanythroughsearch.css');
        Requirements::javascript('fromholdio/silverstripe-gridfield-addnew-manymanythrough: client/js/gridfieldaddnewmanymanythroughsearch.js');

        $data = ArrayData::create([
            'Title' => $this->getTitle(),
            'Classes' => 'action btn font-icon-search add-new-manymanythrough-search ' . $this->getButtonClass(),
            'Link' => $gridField->Link($this->getURLSegment()),
        ]);
        return [$this->fragment => $data->renderWith(__CLASS__)];
    }

    public function getURLHandlers($gridField)
    {
        return [$this->getURLSegment() => 'handleSearch'];
    }

    public function handleSearch($gridField, $request)
    {
        return new GridFieldAddNewManyManyThroughSearchHandler($gridField, $this);
    }

    public function getURLSegment()
    {
        $urlSegment = 'add-new-manymanythrough-search';
        $buttonName = $this->getButtonName();
        if (!is_null($buttonName)) {
            $urlSegment .= '-' . $buttonName;
        }
        return $urlSegment;
    }
}
