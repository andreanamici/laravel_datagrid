@if(count($items) > $per_page)
<tr>
  <th colspan="{{count($cols)}}">
    {{$items->links('datagrid::pagination')}}
  </th>
</tr>
@endif
