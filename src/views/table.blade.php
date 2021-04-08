<form action=""  class="datagrid-form">
    <table {!! $paginator->buildAttributes() !!}>
        @include("datagrid::body")
    </table>
</form>
