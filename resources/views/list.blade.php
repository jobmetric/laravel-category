@extends('panelio::layout.layout')

@section('body')
    <x-list-view name="{{ $name }}" action="{{ $route }}">
        <x-slot name="filter">
            <div class="col-md-3">
                <div class="mb-5">
                    <label class="form-label">نام</label>
                    <input type="text" name="translation[name]" class="form-control filter-list" id="filter-name" placeholder="نام را وارد کنید" value="" autocomplete="off">
                </div>
            </div>
        </x-slot>

        <thead>
            <tr>
                <th width="1%">
                    <div class="form-check form-check-custom form-check-solid">
                        <input class="form-check-input" type="checkbox" value="1" id="check-all"/>
                        <label class="form-check-label ms-0" for="check-all"></label>
                    </div>
                </th>
                <th width="64%">نام</th>
                <th width="10%" class="text-center">وضعیت</th>
                <th width="10%" class="text-center">ترتیب</th>
                <th width="15%" class="text-center">عملیات</th>
            </tr>
        </thead>
    </x-list-view>
@endsection
