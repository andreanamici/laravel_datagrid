<thead class="thead-dark">
    <tr>
        @foreach($cols as $col)
        @if(!$col->isVisible())
        @continue
        @endif
        <th {!! $col->buildAttributes() !!}>
          @if($col->isSortable())
            <a class="sort-link" href='{{$col->orderURL()}}'>{{$col->label()}} @if($col->isSorting()) <i class="{{$col->sortIcon()}}"></i> @endif</a>
          @else
            {{$col->label()}}
          @endif
        </th>
        @endforeach
        @if(!empty($actions))
            <th class="text-center" colspan={{ count($actions) }}>
              <a class="btn btn-secondary btn-default" href="{{ Request::url() }}">{{$btn_reset}}</a>
            </th>
        @else
            @if($paginator->has_filters())
                <th class="text-center">
                  <a class="btn btn-secondary btn-default" href="{{ Request::url() }}">{{$btn_reset}}</a>
                </th>
            @endif
        @endif
    </tr>

    @if($paginator->has_filters())
    <tr style="display:none">
      <td>
          @foreach($paginator->getHiddenFilters() as $key => $hidden_filter)
           <input type="hidden" name="{{$key}}" value="{{request()->input($key)}}" />
          @endforeach
      </td>
    </tr>
    <tr class="table-filters">
    
        @foreach($cols as $col)
       
        @if(!$col->isVisible())
          @if($col->has_filter() && $col->filter()->isVisible())
          <th style="display:none">@include($col->filter()->getView(),['filter' => $col->filter()])</th>
          @endif
        @continue
        @endif
        <th scope="col">
            @if($col->has_filter() && $col->filter()->isVisible())
            @include($col->filter()->getView(),['filter' => $col->filter()])
            @endif
        </th>
        @endforeach
        @if(!empty($actions))
            <td colspan={{ count($actions) }}>
              <input type="submit" class="form-control btn btn-secondary btn-default" value="{{$btn_submit}}" />
            </td>
        @else
            @if($paginator->has_filters())
              <td>
                <input type="submit" class="form-control btn btn-secondary btn-default" value="{{$btn_submit}}" />
              </td>
            @endif
        @endif
    </tr>
    @endif
    </thead>
    