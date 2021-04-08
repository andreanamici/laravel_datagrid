
@if(count($items) > 0)
<tfoot>
    <tr>
        <td colspan="{{count($cols)}}">
          {{$items->links('datagrid::pagination')}}
        </td>
    </tr>
</tfoot>
@endif