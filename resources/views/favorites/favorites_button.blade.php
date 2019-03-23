@if (Auth::id() != $micropost->user_id)
    @if (Auth::user()->is_favorites($micropost->id))
        {!! Form::open(['route' => ['favorites.no_favorites', $micropost->id], 'method' => 'delete']) !!}
            {!! Form::submit('お気に入り解除', ['class' => "btn btn-info btn-sm"]) !!}
        {!! Form::close() !!}
    @else
        {!! Form::open(['route' => ['favorites.yes_favorites', $micropost->id]]) !!}
            {!! Form::submit('お気に入り', ['class' => "btn btn-light btn-sm"]) !!}
        {!! Form::close() !!}
    @endif
@endif