@foreach($warnings as $warning)
    <div class="alert alert-warning" role="alert">
        <i class="fa fa-warning"></i>&nbsp; {{$warning['from'] . ' (- ' . $warning['counter'] . ') : ' . $warning['user'] . __(" is watching ") . $warning['name']}}
    </div>
@endforeach
