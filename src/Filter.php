<?php

namespace WS\Datagrid;

class Filter
{

    protected $mode = "=";
    protected $type;
    protected $name;
    protected $alias;
    protected $modifier;
    protected $parentCol;
    protected $having = false;
    protected $subFilters = [];
    protected $visible = true;

    /**
     * Inizializza un oggetto filtro
     * @param String $type il tipo di filtro
     * @param String $alias TODO: implementare
     */
    public function __construct($type, $alias = null)
    {
        $this->type = $type;
        $this->alias = $alias;
        return $this;
    }
 
    public function isMultiple()
    {
        return count($this->subFilters) > 0;
    }

    public function initChildren()
    {
       foreach($this->subFilters as $filter)
       {
         $filter->setParentCol($this->parentCol);
         if($filter->name() == null)
         {
             $filter->setName($this->parentCol->fieldname());
         }
       }
    }

    /**
     * Aggiunge un filtro alla lista dei sottofiltri, rendendo il genitore un filtro
     * composto.
     */
    public function addSubFilter(Filter $filter)
    {
        $this->subFilters[] = $filter;
    }
    public function getSubFilters()
    {
        return $this->subFilters;
    }
    public function isHaving()
    {
        return $this->having;
    }
    public function setHaving($having=true)
    {
        $this->having = $having;
    }
    public function getColumn()
    {
        return $this->parentCol;
    }
    public function setParentCol(Column $col)
    {
        $this->parentCol = $col;
    }
    /**
     * Funzione utilizzata per passare dati custom alla vista del filtro.
     * Nel filtro base ritorna sempre null, questa funzione è pensata per essere estesa
     */
    public function custom()
    {
        return null;
    }
    
    public function setAlias($alias)
    {
        $this->alias = $alias;
    }

    public function alias()
    {
        return $this->alias ?: $this->name;
    }
    public function setMode($mode)
    {
        $this->mode = $mode;
    }
    /**
     * Ritorna la closure del modificatore. Alla closure ritornata viene passato il valore da modificare
     * prima di applicare il filtro
     * @return Closure la closure modificatore
     */
    public function modifier()
    {
        return $this->modifier;
    }
    /**
     * Ritorna una stringa che indica in che modo opera il filtro
     * @return String modo
     */
    public function mode()
    {
        return $this->mode;
    }

    /**
     * Imposta il nome del filtro
     * @return Filter il filtro corrente
     */
    public function setName($name)
    {
        $this->name = $name;
        if($this->alias ==null)
        {
            $this->alias = str_replace(".","_",$name);
        }
        return $this;
    }

    public function name()
    {
        return $this->name;
    }

    public function getView()
    {
        return "datagrid::filters." . $this->type;
    }
    /**
     * Ritorna true se il filtro ha un modificatore, false altrimenti
     * @return bool
     */
    public function hasModifier()
    {
        return isset($this->modifier);
    }
    /**
     * Imposta la closure modificatore. Questa clousre, se settata, viene invocata prima di eseguire
     * la query e gli viene passato il valore del filtro corrente come argomento, il quale sarà sostituito
     * dal valore ritornato dalla closure
     * @param Closure $modifier la closure
     * @return Filter il filtro corrente
     */
    public function setModifier(\Closure $modifier)
    {
        $this->modifier = $modifier;
        return $this;
    }

    public function fieldname()
    {
        return $this->name;
    }

    public function setVisible(bool $visible){
        $this->visible = $visible;
    }

    public function isVisible(){
        return $this->visible;
    }
}
