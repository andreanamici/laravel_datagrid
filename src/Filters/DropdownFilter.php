<?php
namespace WS\Datagrid\Filters;

use WS\Datagrid\Filter;

class DropdownFilter extends Filter
{
    protected $options = [];

    public function __construct($alias=null)
    {
        parent::__construct('dropdown',$alias);
    }

    public function custom()
    {
        return ['options' => $this->options];
    }

    /**
     * Imposta le option del dropdown
     * @param mixed $option la chiave della option o un array di chiavi => label
     * @param mixed $label  la label della option, può essere omessa se $option è un array
     */
    public function setOption($option,$label=null)
    {
        if(is_array($option))
        {
            foreach($option as $key => $option_child)
            {
                    $this->setOption($key,$option_child);
                    
            }
            return;
        }
        $this->options[$option] = $label;
    }
}