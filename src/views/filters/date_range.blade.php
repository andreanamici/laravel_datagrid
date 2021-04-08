<div class="row">
    <div class="col-lg-6 col-md-6 col-xs-12" style="padding-right:1px">
        <input type="text" name="{{$filter->getFromField()}}" placeholder="Da..." class="th-input datepicker form-control date"
            value="{{request()->input($filter->getFromField())}}">
    </div>
    <div class="col-lg-6 col-md-6  col-xs-12" style="padding-left:1px"> 
        <input type="text" name="{{$filter->getToField()}}" placeholder="A..."
        class="th-input datepicker form-control date ml-2" value="{{request()->input($filter->getToField())}}">
    </div>
</div>