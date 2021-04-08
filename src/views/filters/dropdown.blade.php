<select name="{{$filter->name()}}" class="th-input">
@foreach($filter->custom()['options'] as $key => $value)
<option {{request()->input($filter->alias()) == $key ? 'selected' : null}} value="{{$key}}"> {{$value}}</option>
@endforeach
</select>
