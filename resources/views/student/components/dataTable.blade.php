<table id="{{$id}}" class="display dataTableCn">
    <thead>
    <tr>
        <th>Name</th>
        <th>Position</th>
        <th>Office</th>
        <th>Age</th>
        <th>Start date</th>
        <th>Salary</th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td>Tiger Nixon</td>
        <td>System Architect</td>
        <td>Edinburgh</td>
        <td>61</td>
        <td>2011/04/25</td>
        <td>$320,800</td>
    </tr>
    <tr>
        <td>Tiger Nixon</td>
        <td>System Architect</td>
        <td>Edinburgh</td>
        <td>61</td>
        <td>2011/04/25</td>
        <td>$320,800</td>
    </tr>
    </tbody>
</table>
@push('head')
    <link href="{!! url('bower_components/datatables/media/css/dataTables.bootstrap4.min.css') !!}" rel="stylesheet">
    <script src="{!! url('bower_components/datatables/media/js/jquery.dataTables.js') !!}"></script>
@endpush
@push('scripts-ready')
    $('#{{$id}}').DataTable( {
    "paging":   false,
    "ordering": true,
    "info":     true
    } );
@endpush