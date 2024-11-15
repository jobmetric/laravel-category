@extends('panelio::layout.layout')

@section('body')
    <x-list-view name="{{ $name }}" action="{{ $route }}" export-action="{{ $export_action }}" import-action="{{ $import_action }}">
        @if($show_filter)
            <x-slot name="filter">
                <div class="col-md-3">
                    <div class="mb-5">
                        <label class="form-label">{{ trans('taxonomy::base.list.filters.name.title') }}</label>
                        <input type="text" name="translation[name]" class="form-control filter-list" id="filter-name" placeholder="{{ trans('taxonomy::base.list.filters.name.placeholder') }}" autocomplete="off">
                    </div>
                </div>
                @foreach($metadata as $meta_key => $meta_value)
                    @if(isset($meta_value['has_filter']) && $meta_value['has_filter'])
                        <div class="col-md-3">
                            <div class="mb-5">
                                <label class="form-label">{{ trans($meta_value['label']) }}</label>
                                @if(isset($meta_value['type']) && $meta_value['type'] === 'select')
                                    <select name="{{ $meta_key }}" class="form-control filter-list filter-metadata">
                                        @foreach($meta_value['options'] as $option_key => $option_value)
                                            <option value="{{ $option_key }}">{{ $option_value }}</option>
                                        @endforeach
                                    </select>
                                @endif
                                @if(isset($meta_value['type']) && $meta_value['type'] === 'text')
                                    <input type="text" name="{{ $meta_key }}" class="form-control filter-list filter-metadata" placeholder="{{ trans($meta_value['placeholder']) }}" autocomplete="off">
                                @endif
                                @if(isset($meta_value['type']) && $meta_value['type'] === 'number')
                                    <input type="number" name="{{ $meta_key }}" class="form-control filter-list filter-metadata" placeholder="{{ trans($meta_value['placeholder']) }}" autocomplete="off">
                                @endif
                            </div>
                        </div>
                    @endif
                @endforeach
            </x-slot>
        @endif

        <thead>
            <tr>
                <th width="1%"></th>
                <th width="1%">
                    <div class="form-check form-check-custom form-check-solid">
                        <input class="form-check-input" type="checkbox" value="1" id="check-all"/>
                        <label class="form-check-label ms-0" for="check-all"></label>
                    </div>
                </th>
                <th width="6%" class="text-center text-gray-800">{{ trans('package-core::base.list.columns.image') }}</th>
                <th width="42%" class="text-gray-800 auto-width-content">{{ trans('package-core::base.list.columns.name') }}</th>
                <th width="10%" class="text-center text-gray-800">{{ trans('package-core::base.list.columns.status') }}</th>
                <th width="10%" class="text-center text-gray-800">{{ trans('package-core::base.list.columns.ordering') }}</th>
                <th width="15%" class="text-center text-gray-800">{{ trans('package-core::base.list.columns.translations') }}</th>
                <th width="15%" class="text-center text-gray-800">{{ trans('package-core::base.list.columns.action') }}</th>
            </tr>
        </thead>
    </x-list-view>

    <h6 class="mt-10">{{ $description ?? '' }}</h6>
@endsection
