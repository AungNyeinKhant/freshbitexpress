@foreach($rl as $k=>$r)
<tr>
    <td scope="row">{{$k+1}}</td>
    <td>{{Str::limit($r['name'],20,'...')}}</td>
    <td class="text-capitalize">
        @if($r['modules']!=null)
            @foreach((array)json_decode($r['modules']) as $key=>$m)
               {{ translate( str_replace('_',' ',$m))}},
            @endforeach
        @endif
    </td>
    <td>
        {{  Carbon\Carbon::parse($r['created_at'])->locale(app()->getLocale())->translatedFormat('d M Y') }}

        {{-- {{date('d-M-y',strtotime($r['created_at']))}} --}}
    </td>
    {{--<td>
        {{$r->status?'Active':'Inactive'}}
    </td>--}}
    <td>
        <div class="btn--container justify-content-center">
            <a class="btn action-btn btn--primary btn-outline-primary"
                href="{{route('vendor.custom-role.edit',[$r['id']])}}" title="{{translate('messages.edit_role')}}"><i class="tio-edit"></i>
            </a>
            <a class="btn action-btn btn--danger btn-outline-danger" href="javascript:"
                onclick="form_alert('role-{{$r['id']}}','{{translate('messages.Want_to_delete_this_role')}}')" title="{{translate('messages.delete_role')}}"><i class="tio-delete-outlined"></i>
            </a>
            <form action="{{route('vendor.custom-role.delete',[$r['id']])}}"
                    method="post" id="role-{{$r['id']}}">
                @csrf @method('delete')
            </form>
        </div>
    </td>
</tr>
@endforeach
