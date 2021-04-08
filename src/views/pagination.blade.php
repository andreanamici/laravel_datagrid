<nav class="pagination-container">
    {{$paginator->onEachSide(config('datagrid.totPages'))->links()}}
</nav>