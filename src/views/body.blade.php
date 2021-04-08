@include("datagrid::head")

<tbody>
  @forelse($items as $item)
  <tr>
    @foreach($cols as $col)
    @if(!$col->isVisible())
        @continue
    @endif
    <td {!! $col->buildAttributes() !!}>
      {{$col->applyRoute($item)}}
     
      @if($col->url())
      <a href="{{$col->url()}}">
        @endif
        {!! $col->field($item) !!}
        @if($col->url())
      </a>
      @endif
    </td>
    @endforeach
    @foreach($actions as $action)
    <td class="table-action-td">
      {!! $action->render($item) !!}
    </td>
    @endforeach
  </tr>
  @empty
    @if($empty_message)
    <tr>
        <td colspan="{{ count($cols) + count($actions)  }}" class="empty-row">{{$empty_message}}</td>
    </tr>
    @endif
  @endforelse
</tbody>
@include("datagrid::footer")