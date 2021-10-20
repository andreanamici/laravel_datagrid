<?php

namespace WS\Datagrid;
use Illuminate\Support\Arr;
use Closure;
use Illuminate\Database\Eloquent\Model;
use WS\Datagrid\Exceptions\LoopInterruptException;

class Datagrid
{
    private $columns = [];
    private $footer = null;

    private $model;
    private $items;
    private $actions = [];
    private $perPage;
    private $filters = [];
    private $hidden_filters = [];
    private $name = null;
    private $authService;
    private $items_count;
    private $prefetch;
    private $emptyMessage;
    private $attributes = ['class' => 'table table-sm datagrid'];

    private $data = [];

    public function __construct($model)
    {
        $this->model = $model;
        $this->perPage = config('datagrid.perPage');
        $auth = config('datagrid.authService');
        $authService = new $auth;

        $this->setAuthService($authService);
        return $this;
    }

    /**
     * Imposta la classe che gestisce i permessi del datagrid
     * @param AuthInterface $authService
     */
    public function setAuthService(AuthInterface $authService)
    {
        $this->authService = $authService;
        return $this;
    }

    /**
     * Ritorna un istanza dell'authService del datagrid
     */
    public function getAuthService()
    {
        return $this->authService;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function perPage($perPage)
    {
        $this->perPage = $perPage;
    }

    /**
     * Imposta uno o più attributi al grid
     *
     * @param mixed $attr il nome dell'attributo o un array di attributi con i rispettivi valori
     * @param mixed $value il valore dell'attributo, non specificare se si passa un array come primo argomento
     *
     * @return DataGrid
     */
    public function setAttribute($attr, $value = null)
    {
        switch ($attr) {
            case is_array($attr):
                foreach ($attr as $key => $value) {
                    $this->setAttribute($key, $value);
                }
                break;
            default:
                $this->attributes[$attr] = $value;
        }

        return $this;
    }

    public function attributes()
    {
        return $this->attributes;
    }

    public function buildAttributes()
    {
        $attrs = "";

        foreach ($this->attributes as $attr => $value) {
            $attrs .= $attr . "=\"{$value}\" ";
        }
        return $attrs;
    }

    protected function _apply_raw()
    {        
        if(!$this->model->getQuery()->columns){
            $this->model = $this->model->addSelect("*");
        }

        foreach ($this->columns as $col) {

            if (!$col->isRaw()) {
                continue;
            }

            $this->model = $this->model->selectRaw($col->rawFieldname() . " as " . $col->fieldname());
        }
    }
    public function prefetch(Closure $function)
    {
        $this->prefetch = $function;
        return $this;
    }

    protected function _fetch()
    {
        if(!is_null($this->items)){
            return $this;
        }

        if (!is_object($this->model)) {
            $this->model = new $this->model;
        }
        $this->_apply_raw();
        if($this->prefetch){
           $pref = $this->prefetch;
           $pref($this->model);
        }
        $this->_apply_filters();
        $this->_apply_order();
        
        $queryString = Arr::except(\Request::query(), "page");
        $count = $this->model->count();
        if (request()->get('per_page') != null) {
            $this->perPage = request()->get('per_page');
        }

        $this->items = $this->model->paginate($this->perPage == false ? $count : $this->perPage);

        $this->items->appends($queryString);

        $this->emptyMessage = $count == 0 ? ($this->has_filters() ? \Lang::get('datagrid::datagrid.empty_search') : \Lang::get('datagrid::datagrid.empty_items')) : $this->emptyMessage;
    }

    protected function _apply_order()
    {
        foreach ($this->columns as $column) {

            if ($column->isSorting()) {

                $this->model = $this->model->orderBy(str_replace('.', '_', $column->fieldname()), $column->sortMode());
            }
        }
    }

    protected function _apply_filter($filter, $value)
    {
        if ($filter->hasModifier()) {
            $mod = $filter->modifier();
            $value = $mod($value);
        }
        if ($filter->mode() == "like") {
            $value   = "%" . $value . "%";
        }
        $mode = $filter->isHaving() ? 'having' : 'where';
        $modeRaw = $filter->isHaving() ? 'havingRaw' : 'whereRaw';

        if (strpos($filter->fieldname(), ".") !== false && $filter->getColumn()->isRelation()) {
            $parts =  explode(".", $filter->fieldname());
            $key = $parts[count($parts) - 1];
            array_pop($parts);
            $relation =  implode(".", $parts);

            $this->model = $this->model->whereHas($relation,  function ($q) use ($key, $filter, $value, $mode, $modeRaw) {
                if ($filter->getColumn()->isRaw()) {
                    $q->{$modeRaw}($key . " " . $filter->mode() . " ?", [$value]);
                    return;
                }
                $q->{$mode}($key, $filter->mode(), $value);
            });

            throw new LoopInterruptException();
        }
        if ($filter->getColumn()->isRaw()) {
            $this->model = $this->model->{$modeRaw}($filter->getColumn()->rawFieldname() . " " . $filter->mode() . " ?", [$value]);
            throw new LoopInterruptException();
        }
        $this->model = $this->model->{$mode}($filter->getColumn()->rawFieldname(), $filter->mode(), $value);
    }

    protected function _apply_filters()
    {
        foreach (\Request::input() as $filter => $value) {

            if ($value == null || !array_key_exists(str_replace(".", "_", $filter), $this->filters)) {
                continue;
            }

            $filter = $this->filters[$filter];
            if (!$filter->isVisible() || $filter->alias() != str_replace(".", "_", $filter->name())) {
                $this->hidden_filters[$filter->alias()] = $filter;
            }

            try {
                if ($filter->isMultiple()) {
                    foreach ($filter->getSubFilters() as $f) {
                        if (!array_key_exists($f->alias(), $this->filters)) {
                            $this->_apply_filter($f, $value);
                        }
                    }
                    continue;
                }
                $this->_apply_filter($filter, $value);
            } catch (LoopInterruptException $e) {
                continue;
            }
        }
    }

    public function addAction(Action $action)
    {
        $action->setParent($this);
        $this->actions[] = $action;
        return $this;
    }

    /**
     * Sposta una colonna alla posizione fornita come argomento
     * @param Column $column la colonna da spostare
     * @param mixed $position la nuova posizione
     */
    public function orderColumn(Column $column, $position)
    {
        $curIndex = array_search($column, $this->columns);
        unset($this->columns[$curIndex]);
        $lastIndex = count($this->columns) - 1;
        $position = max(0, min($lastIndex, $position));


        $this->columns = array_slice($this->columns, 0, $position, true) + [$position => $column] + array_slice($this->columns, $position, $lastIndex, true);
    }

    public function getColumns()
    {
        return $this->columns;
    }

    public function addColumn(Column $column)
    {
        $column->setParent($this);
        $this->columns[] = $column;

        if ($column->has_filter()) {
            $this->filters[$column->filter()->alias()] = $column->filter();
            if ($column->filter()->isMultiple()) {
                foreach ($column->filter()->getSubFilters() as $subfilter) {
                    if ($subfilter->alias() != $column->filter()->alias()) {
                        $this->filters[$subfilter->alias()] = $subfilter;
                    }
                }
            }
        }

        return $this;
    }

    public function addFooter(Footer $footer)
    {
        $this->footer = null;
        if ($footer->refColumn() == null)
        {
            return;
        }

        // verifico se c'è la colonna
        foreach ($this->columns as $col) {
            if ($col->fieldname() == $footer->refColumn()) {
                $this->footer = $footer;
                return true;
            }
        }


        return false;
    }

    public function has_filters()
    {
        foreach ($this->columns as $col) {
            if ($col->has_filter() && $col->filter()->isVisible()) {
                return true;
            }
        }
        return false;
    }

    protected function _parse()
    {
        $this->_fetch();
        $data = [];
        $data['items'] = $this->items;
        $this->items_count = count($this->items);
        $data['cols'] = $this->columns;
        $data['footer'] = $this->footer;
        $data['per_page'] = $this->perPage;
        $data['paginator'] = $this;
        $data['actions'] = [];
        $data['empty_message'] = $this->emptyMessage;

        foreach ($this->actions as $action) {
            if ($action->canBeDrawn()) {
                $data['actions'][] = $action;
            }
        }
        return $data;
    }

    public function collect()
    {
        $this->_fetch();
        return $this->items;
    }

    public function countItems()
    {
        return $this->items_count;
    }

    public function getItems()
    {
        return $this->items;
    }


    /**
     * Risale ad un elemento dell'item passato come argomento utilizzando il parametro flag come chiave.
     * Possono essere usati i punti(.) come separatore per scendere di livello tra relazioni.
     * @param Object $item la riga/oggetto da risolvere
     * @param String $flag la chiave
     * @return mixed il valore trovato o null
     */
    public static function resolve($item, $flag)
    {
        $flags =  explode(".", $flag);
        foreach ($flags as $part) {
            if (!$item) {
                return null;
            }
            $item = $item->{$part};
        }
        return $item;
    }

    public function parse()
    {
        $data = $this->_parse();
        $this->data = $data;
    }

    public function render($parse=true)
    {
        $data =  $parse === true ? $this->_parse() : $this->data;
        $view = \Illuminate\Support\Facades\View::make('datagrid::table', $data);
        return $view->render();
    }

    public function getHiddenFilters()
    {
        return $this->hidden_filters;
    }
}
