<?php
namespace WS\Datagrid;

class Footer
{
    private $visible = true;
    private $refColumn = null;
    private $value = null;
    private $attributes = [];

    /**
     * Datagrid instanziato
     * @var Datagrid
     */
    private $parent;
    
    public function __construct($label)
    {
        $this->refColumn = $label;
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
     * @return Footer 
     */
    public function setVisible(bool $visible)
    {
        $this->visible = $visible;
        return $this;
    }


    /**
     * Imposta il contenuto da mostrare
     * @return Footer 
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * Ritorna il contenuto da mostrare
     */
    public function value()
    {
        return $this->value;
    }

    /**
     * Imposta il contenuto da mostrare
     * @return Footer 
     */
    public function setRefColumn($column)
    {
        $this->refColumn = $column;
        return $this;
    }

    /**
     * Ritorna il contenuto da mostrare
     */
    public function refColumn()
    {
        return $this->refColumn;
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
        foreach ($this->attributes as $attr => $value) {
            $attrs[] = "{$attr}=\"{$value}\"";
        }
        
        return implode(' ',$attrs);
    }
}