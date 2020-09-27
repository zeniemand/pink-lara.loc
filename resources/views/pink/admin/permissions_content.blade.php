<div id="content-page" class="content group">
    <div class="hentry group">
        <h3 class="title_page"> Привелегии </h3>

        <form action="{{ route('admin.permissions.store') }}" method="post">
            {{ csrf_field() }}

            <div class="short-table white">

                <table style="width:100%">
                    <thead>
                        <th>Привелегии</th>
                        @if(!$roles->isEmpty())
                            @foreach($roles as $item)
                                <th>{{ $item->name }}</th>
                            @endforeach
                        @endif
                    </thead>
                    <tbody>

                        @if(!$permissions->isEmpty())
                            @foreach($permissions as $permission)
                                <tr>
                                    <td>{{ $permission->name }}</td>
                                        @foreach($roles as $role)
                                            <td>
                                                @if($role->hasPermission($permission->name))
                                            <input checked name="{{ $role->id }}[]" type="checkbox" value="{{ $permission->id }}">
                                                @else
                                            <input name="{{ $role->id }}[]" type="checkbox" value="{{ $permission->id }}">
                                                @endif
                                            </td>
                                        @endforeach
                                </tr>
                            @endforeach
                        @endif

                    </tbody>


                </table>
            </div>

            <input type="submit" class="btn btn-the-salmon-dance-3" value="Обновить">

        </form>

    </div>
</div>