<nav class="pagination-container d-flex justify-content-end">
  <ul class="pagination">
      @if ($paginator->lastPage() > 1)
          <li class="page-item {{ ($paginator->currentPage() == 1) ? ' disabled' : '' }}">
              <a class="page-link" href="{{ $paginator->url(1) }}"><i class="{{ config('datagrid.icons.pagination_left') }}"></i></a>
          </li>
          @for ($i = 1; $i <= $paginator->lastPage(); $i++)
              <li class="page-item {{ ($paginator->currentPage() == $i) ? ' active' : '' }}">
                  <a class="page-link" href="{{ $paginator->url($i) }}">{{ $i }}</a>
              </li>
          @endfor
          <li class="page-item {{ ($paginator->currentPage() == $paginator->lastPage()) ? ' disabled' : '' }}">
              <a class="page-link" href="{{ $paginator->url($paginator->currentPage()+1) }}"><i class="{{ config('datagrid.icons.pagination_right') }}"></i></a>
          </li>
         
      @endif
  </ul>
</nav>
<div class="d-flex justify-content-end">
    <span class="display_total"> {{ $paginator->total() }} risultati</span>
</div>
