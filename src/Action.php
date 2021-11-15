<?php

namespace WS\Datagrid;

use \App\Services\Auth;
use Closure;

class Action
{
    private $icon = null;

    private $label = null;
    private $condition = null;
    private $permissions = [];
    private $attributes = [];
    private $urlModifier;
    private $route;
    private $routeAttrs;
    private $routeParams;
    private $parent; // il datagrid parent
    private $modifier = null;
    private $customRender;

    /**
     * Costruttore dell'azione, accetta come argomento la closure che definisce l'url a cui puntare
     * @param Closure $urlModifier closure che definisce l'url a cui punta l'action.Accetta come argomento
     * la riga corrente
     */
    public function __construct(\Closure $urlModifier = null)
    {
        $this->urlModifier = $urlModifier;
    }

    /**
     * Imposta il datagrid padre della colonna
     */
    public function setParent(Datagrid $parent)
    {
        $this->parent = $parent;
    }

    /**
     * Imposta la rotta a cui punta l'azione, se settata sovrascrive la closure definita nel costruttore
     * @param mixed $route  la rotta
     * @param array $attrs  un array di attributi, i valori devono rispecchiare eventuali colonne presenti nel datagrid
     * @param array $params un array di parametri passati alla rotta
     */
    public function setRoute($route,array $attrs, array $params = [])
    {
        $this->route = $route;
        $this->routeAttrs = $attrs;
        $this->routeParams = $params;
    }

    public function setCustomRender(Closure $callback)
    {
        $this->customRender = $callback;
    }

    /**
     * Imposta gli attributi dell'action
     * @param mixed $attr il nome dell'attributo o un array di attributi
     * @param mixed $value il valore dell'attributo, non necessario in caso si passi un array 
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

    public function setPermission($label)
    {
        if (is_array($label)) {
            foreach ($label as $item) {
                $this->setPermission($item);
            }
            return $this;
        }
        $this->permissions[] = $label;
        return $this;
    }

    /**
     * Imposta una condizione per l'azione
     * @param Closure $condition closure
     * Accetta come argomento la riga corrente 
     * @return Action Ritorna l'azione corrente
     */
    public function setCondition(\Closure $condition)
    {
        $this->condition = $condition;
        return $this;
    }

    public function canBeDrawn()
    {
        foreach ($this->permissions as $permission) {
            if ($this->parent->getAuthService()->cant($permission)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Imposta l'icona dell'azione, basata su fontawesome
     * @param mixed $icon il tag font-awesome
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;
    }

    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * Imposta un modificatore per il contenuto dell'azione
     * @param Closure $modifier una closure che viene chiamata per trasformare il valore preimpostato dell'azione.
     * Accetta come primo argomento il campo(passarlo per referenza se lo si vuole modificare) e come secondo argomento 
     * la riga corrente
     * @return Column L'azione corrente corrente
     */
    public function setModifier(\Closure $modifier)
    {
        $this->modifier = $modifier;
        return $this;
    }


    private function replace_placeholders($item)
    {
        foreach ($item as $key => $value) {
            $this->url = preg_replace("/{$key}/", $value, $this->url);
        }
    }

    public function getRoute($item)
    {
        $params = [];
        foreach($this->routeAttrs as $param)
        {
            $params[] = $item->{$param};
        }

        $params = array_merge($params, $this->routeParams);
        
        return route($this->route,$params);
    }

    public function render($item)
    {
        if ($this->condition) {
            $condition = $this->condition;
            $return = $condition($item);

            if ($return === false) {
                    return;
            }
        }

        $data['icon'] = $this->icon;
        
        if($this->customRender)
        {
            $customRender = $this->customRender;
            return $customRender($item);
        }

        $urlModifier = $this->urlModifier;
        $url = $this->route ? $this->getRoute($item) : ($urlModifier ? $urlModifier($item) : null);
        $data['url'] = $url;
        $data['label'] = $this->label;
        $data['attributes'] = $this->buildAttributes();

        $view = \Illuminate\Support\Facades\View::make('datagrid::actions.base', $data);
        $output = $view->render();

        if ($this->modifier) 
        {
            $mod = $this->modifier;
            $mod($output, $item);
        }

        return $output;
    }
}
