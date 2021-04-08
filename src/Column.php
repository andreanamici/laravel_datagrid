<?php
namespace WS\Datagrid;

use Illuminate\Database\Query\Expression;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class Column
{
    private $visible = true;
    private $label = "";
    private $fieldname = "";
    private $rawFieldname;
    private $filter = null;
    private $filter_mode = "="; //TODO: DEPRECATO:: da rimuovere in versioni future
    private $filter_alias = null; //TODO: DEPRECATO:: da rimuovere in versioni future
    private $attributes = [];
    private $isSorting = false;
    private $sortMode = 'ASC';
    private $modifier = null;
    private $url = null;
    private $route = null;
    private $routeParams = [];
    private $isRelation = true;
    private $sortable = true;

    /**
     * Datagrid instanziato
     * @var Datagrid
     */
    private $parent;

    public function __construct($label)
    {
        $this->label = $label;
        $this->fieldname = $label;
        return $this;
    }

    /**
     * Imposta la rotta associata al colonna corrente
     */
    public function setRoute($route, array $params = [])
    {
        $this->route = $route;
        $this->routeParams = $params;
        return $this;
    }

    /**
     * Imposta il datagrid padre della colonna
     */
    public function setParent(Datagrid $parent)
    {
        $this->parent = $parent;
    }

    /**
     * Imposta la colonna come visibile o meno
     * @param bool $visible
     * @return Column
     */
    public function setVisible(bool $visible)
    {
        $this->visible = $visible;
        return $this;
    }

    /**
     * Controlla se la colonna è visibile
     * @return bool true se visible, altrimenti false
     */
    public function isVisible()
    {
        return $this->visible;
    }

    /**
     * Genera la rotta per la colonna corrente
     * @param $item La riga corrente, usata per fetchare i parametri della rotta
     */
    public function applyRoute($item)
    {
        if (!$this->route) {
            return;
        }
        $params = [];
        foreach ($this->routeParams as $param) {
            $params[] = Datagrid::resolve($item, $param);
        }
        $this->url = route($this->route, $params);
    }
    /**
     * Ritorna l'url a cui punta la colonna
     * @return String url a cui punta la colonna, può essere null se non settato
     */
    public function url()
    {

        return $this->url;
    }

    public function sort($mode = 'ASC')
    {
        $this->isSorting = true;
        $this->sortMode = $mode;
    }

    public function sortName()
    {
        return 'order-' . str_replace(".", "_",$this->fieldname());
    }

    public function sortIcon()
    {
        if (!$this->isSorting()) {
            return null;
        }
        $icon = null;
        switch ($this->sortMode()) {
            case "ASC":
                $icon = config('datagrid.icons.sort_up');
                break;
            case "DESC":
                $icon = config('datagrid.icons.sort_down');
                break;
        }

        return $icon;
    }
    public function orderURL()
    {
        if (!$this->isSortable())
        {
            return false;
        }

        $name = $this->sortName();
        $q = Arr::except(\Request::query(), $name);
        $keys = array_keys($q);

        foreach ($keys as $key) {
            if (!preg_match('/^order\-/', $key)) {
                if (is_array($q[$key]))
                {
                    $qq = [];
                    foreach($q[$key] as $kk => $vv)
                    {
                        $qq[] = $key . "[]=" . $vv;
                    }

                    $q[$key] = implode("&",$qq);
                }
                else
                {
                    $q[$key] = $key . "=" . $q[$key];
                }
                
            } 
            else {
                unset($q[$key]);
            }
        }

        $prev = request()->get($name);
        $new = $prev == 'ASC' ? 'DESC' : 'ASC';
        $q[] = $name . "=" . $new;

        $qs = implode("&", $q);
        return request()->url() . "?" . $qs;
    }

    /**
     * Imposta la colonna come ordinabile o meno
     * @param bool $sortable
     * @return Column
     */
    public function setSortable(bool $sortable)
    {
        $this->sortable = $sortable;
        return $this;
    }

    /**
     * Controlla se la colonna è ordinabile
     * @return bool true se ordinabile, altrimenti false
     */
    public function isSortable()
    {
        return $this->sortable;
    }

    public function isSorting()
    {
        if (!$this->isSortable())
        {
            return false;
        }

        //Verifico che altre colonne siano ordinate
        $columns      = $this->parent->getColumns();
        $columnSorted = false;

        if($columns){
            foreach($columns as $column){/*@var $column Column*/
                if(request()->get($column->sortName())){
                    $columnSorted = true;
                }
            }
        }

        //Nella request ho richiesto l'ordinamento su questa colonna oppure questa colonna è quella ordinata
        if (request()->get($this->sortName()) != null || (!$columnSorted && $this->isSorting)) {
            return true;
        }

        return false;
    }

    public function sortMode()
    {
        return request()->get($this->sortName()) ? request()->get($this->sortName()) : $this->sortMode;
    }

    /**
     * Imposta un modificatore per la colonna
     * @param Closure $modifier una closure che viene chiamata per trasformare il valore corrente della colonna.
     * Accetta come primo argomento il campo(passarlo per referenza se lo si vuole modificare) e come secondo argomento
     * la riga corrente
     * @return Column La colonna corrente
     */
    public function setModifier(\Closure $modifier)
    {
        $this->modifier = $modifier;
        return $this;
    }

    /**
     * Imposta uno o più attributi della colonna
     * @param mixed $attr il nome dell'attributo o un array di attributi con i rispettivi valori
     * @param mixed $value il valore dell'attributo, non specificare se si passa un array come primo argomento
     * @return Column
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
        $attrs = [];

        $this->attributes['class'] = isset($this->attributes['class']) ? $this->attributes['class'] : '';

        $colclass = str_replace(array('.','_'),'-','gridcol-'.$this->fieldname);

        if(strstr($this->attributes['class'], $colclass) === false){
            $this->attributes['class'] = $colclass . ' ' . $this->attributes['class'];
        }

        foreach ($this->attributes as $attr => $value) {
            $attrs[] = "{$attr}=\"{$value}\"";
        }

        return implode(' ',$attrs);
    }

    public function isRaw()
    {
        return $this->rawFieldname != null;
    }
    public function rawFieldname()
    {
        return $this->rawFieldname ?: $this->fieldname;
    }
    /**
     * Imposta il fieldname come raw sql
     * @param mixed una stringa che sarà passata come raw sql
     * @param mixed $alias l'alias con cui ottenere il campo
     * @param Boolean $relation se true tiene conto dei punti nel nome come se fosse una relazione e non un nome di tabella
     */
    public function setRawFieldname($rawExpression,$alias,$relation=true)
    {
        $this->setFieldname($alias);
        $this->rawFieldname = $rawExpression;
        $this->isRelation = $relation;
    }

    public function isRelation()
    {
        return $this->isRelation;
    }
    /**
     * Imposta il fieldname della colonna
     * @param mixed $fieldname il fieldname
     * @param mixed $relation se true tiene conto dei punti nel nome come se fosse una relazione e non un nome di tabella
     */
    public function setFieldname($fieldname,$relation=true)
    {
        $this->fieldname = $fieldname;
        $this->isRelation = $relation;
        return $this;
    }

    public function fieldname()
    {
        return $this->fieldname;
    }

    protected function _resolveField($item, $fieldName)
    {
        $elements = explode(".", $fieldName);

        if (count($elements) == 1) {
            return $item->{$elements[0]};
        } else {
            $value = $item;
            foreach ($elements as $el) {
                if (!is_object($value)) {
                    return null;
                } else {
                    $value = $value->{$el};
                }
            }

            return $value;
        }
    }


    public function field($item)
    {
        $field = $this->_resolveField($item, $this->fieldname());

        if ($this->modifier) {
            $mod = $this->modifier;
            $mod($field, $item);
        }

        return $field;
    }

    public function label()
    {
        return $this->label;
    }

    public function setFilter($filter)
    {
        if (!($filter instanceof Filter)) { // TODO: Rimuovere in versioni future, inserito per retrocompatibilità
            $filter = new Filter($filter);
        }
        if ($filter->name() == null) {

            $filter->setName($this->fieldname());
        }
        $filter->setParentCol($this);
        if($filter->isMultiple())
        {
            $filter->initChildren();
        }
        $this->filter = $filter;

        return $this;
    }

    public function setFilterMode($mode) //TODO: rimuovere funzione deprecata
    {
        $this->filter->setMode($mode);
    }

    public function filterMode() //TODO: rimuovere funzione deprecata
    {
        return $this->filter->mode();
    }

    public function setFilterAlias($filterAlias)   //TODO: rimuovere funzione deprecata
    {
        $this->filter_alias = $filterAlias;
        return $this;
    }

    public function filter()
    {
        return $this->filter;
    }

    public function filterAlias()  //TODO: rimuovere funzione deprecata
    {
        return $this->filter_alias;
    }

    public function filterName()  //TODO: rimuovere funzione deprecata
    {
        $alias = $this->filterAlias();

        return $alias !== null ? $alias : $this->fieldname();
    }



    public function has_filter()
    {
        return $this->filter != null;
    }
}
