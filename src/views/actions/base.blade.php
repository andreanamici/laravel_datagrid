<a href='{{$url}}' {!! $attributes !!}>
@isset($icon)
<i class="fa fa-{{$icon}}"></i>
@endisset
@if(!isset($icon) && isset($label))
{{$label}}
@endif
</a>
