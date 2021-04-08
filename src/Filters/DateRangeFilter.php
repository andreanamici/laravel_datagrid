<?php

namespace WS\Datagrid\Filters;

use WS\Datagrid\Filter;

use Carbon\Carbon;

class DateRangeFilter extends Filter
{
    protected $from = '';
    protected $to = '';

    public function __construct($alias = null)
    {
        parent::__construct('date_range', $alias);
    }


    public function setRange($from, $to)
    {
        $this->from = $from;
        $this->to = $to;

        $startFilterFrom = new Filter('text');
        $startFilterFrom->setMode('>=');
        $startFilterFrom->setAlias($from);

        $startFilterFrom->setModifier(function ($item) use ($from) {
            $item =  Carbon::parse(request()->input($from))->format('Y-m-d');
            return $item;
        });


        $startFilterTo = new Filter('text');
        $startFilterTo->setMode('<=');
        $startFilterTo->setAlias($to);
        $startFilterTo->setModifier(function ($item) use ($to) {
            $item =  Carbon::parse(request()->input($to))->format('Y-m-d');
            return $item;
        });

        $this->addSubFilter($startFilterFrom);
        $this->addSubFilter($startFilterTo);
        

    }

    public function getFromField(){
        return $this->from;
    }

    public function getToField(){
        return $this->to;
    }
}
