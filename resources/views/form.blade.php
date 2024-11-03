@extends('panelio::layout.layout')

@section('body')
    <form method="post" action="{{ $action }}" class="form d-flex flex-column flex-lg-row" id="form">
        @csrf
        <input type="hidden" name="type" value="{{ $type }}">
        <div class="d-flex flex-column gap-7 gap-lg-10 w-100 w-lg-300px mb-7 me-lg-10">

            @if($has_base_media || !empty($media))
                <!--begin::image-->
                <x-file-manager>
                    @if ($has_base_media)
                        <x-file-single name="{{ trans('category::base.form.media.base.title') }}" collection="base" mime-types="image" value="{{ old('media.base') }}" />
                    @endif

                    @foreach($media as $media_collection => $media_item)
                        @if($media_item['multiple'])
                            <x-file-multiple name="{{ trans('category::base.form.media.' . $media_collection . '.title') }}" collection="{{ $media_collection }}" mime-types="{{ implode(',', $media_item['mime_types']) }}" value="{{ implode(',', old('media.' . $media_collection, [])) }}" />
                        @else
                            <x-file-single name="{{ trans('category::base.form.media.' . $media_collection . '.title') }}" collection="{{ $media_collection }}" mime-types="{{ implode(',', $media_item['mime_types']) }}" value="{{ old('media.' . $media_collection) }}" />
                        @endif
                    @endforeach
                </x-file-manager>
                <!--end::image-->
            @endif

            <!--begin::Slug-->
            <div class="card card-flush py-4">
                <div class="card-header">
                    <div class="card-title">
                        <span class="fs-5 fw-bold">{{ trans('url::base.fields.slug.title') }}</span>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <input type="text" name="slug" class="form-control" placeholder="{{ trans('url::base.fields.slug.placeholder') }}" value="{{ old('slug') }}">
                    @error('slug')
                        <div class="form-errors text-danger fs-7 mt-2">{{ $message }}</div>
                    @enderror
                    <div class="mt-5 text-gray-600 fs-7">{{ trans('url::base.fields.slug.description') }}</div>
                </div>
            </div>
            <!--end::Slug-->

            <!--begin::Status-->
            <div class="card card-flush py-4">
                <div class="card-header">
                    <div class="card-title">
                        <span class="fs-5 fw-bold">{{ trans('package-core::base.status.label') }}</span>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <select name="status" class="form-select" data-control="select2" data-hide-search="true">
                        <option value="1" @if(old('status') == '1') selected @endif>{{ trans('package-core::base.status.enable') }}</option>
                        <option value="0" @if(old('status') == '0') selected @endif>{{ trans('package-core::base.status.disable') }}</option>
                    </select>
                    @error('status')
                        <div class="form-errors text-danger fs-7 mt-2">{{ $message }}</div>
                    @enderror
                    <div class="mt-5 text-gray-600 fs-7">{{ trans('package-core::base.status.description') }}</div>
                </div>
            </div>
            <!--end::Status-->
        </div>

        <div class="d-flex flex-column flex-row-fluid gap-7 gap-lg-10">
            <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x border-0 fs-4 fw-semibold mb-n2 d-flex justify-content-between align-items-center">
                <div class="d-flex">
                    <li class="nav-item">
                        <a class="nav-link text-active-primary pb-4 active" data-bs-toggle="tab" href="#tab_general">{{ trans('package-core::base.tabs.general') }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-active-primary pb-4" data-bs-toggle="tab" href="#tab_option">{{ trans('package-core::base.tabs.options') }}</a>
                    </li>
                </div>
                <div class="d-flex">
                    <li class="nav-item">
                        <a class="nav-link text-active-primary pb-4" data-bs-toggle="tab" href="#tab_layout">{{ trans('package-core::base.tabs.layout') }}</a>
                    </li>
                </div>
            </ul>

            <div class="tab-content">
                <div class="tab-pane fade show active" id="tab_general">
                    <div class="d-flex flex-column gap-7 gap-lg-10">
                        <!--begin::General Name-->
                        <div class="card card-flush py-4">
                            <div class="card-header">
                                <div class="card-title">
                                    <span class="fs-5 fw-bold">{{ trans('package-core::base.cards.general_info') }}</span>
                                </div>
                                <div class="card-toolbar">
                                    <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x fs-6 border-0">
                                        <li class="nav-item">
                                            <a class="nav-link active" data-bs-toggle="tab" href="#tab_general_public">{{ trans('package-core::base.tabs.basic_info') }}</a>
                                        </li>
                                        @if(isset($translation['seo']) && $translation['seo'])
                                            <li class="nav-item">
                                                <a class="nav-link" data-bs-toggle="tab" href="#tab_general_seo">{{ trans('package-core::base.tabs.seo') }}</a>
                                            </li>
                                        @endif
                                    </ul>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="tab-content">
                                    <div class="tab-pane fade show active" id="tab_general_public" role="tabpanel">
                                        @if(isset($translation['fields']['name']))
                                            <div class="mb-10">
                                                <label class="form-label d-flex justify-content-between align-items-center">
                                                    <span class="required">{{ trans($translation['fields']['name']['label']) }}</span>
                                                    <div class="text-gray-600 fs-7 d-none d-md-block d-lg-none d-xl-block">{{ trans($translation['fields']['name']['info']) }}</div>
                                                </label>
                                                <input type="text" name="translation[name]" class="form-control" placeholder="{{ trans($translation['fields']['name']['placeholder']) }}" value="{{ old('translation.name') }}">
                                                <div class="text-gray-600 fs-7 mt-2 d-md-none d-lg-block d-xl-none">{{ trans($translation['fields']['name']['info']) }}</div>
                                                @error('translation.name')
                                                    <div class="form-errors text-danger fs-7 mt-2">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        @else
                                            <div class="mb-10">
                                                <label class="form-label d-flex justify-content-between align-items-center">
                                                    <span class="required">{{ trans('translation::base.fields.name.label') }}</span>
                                                    <div class="text-gray-600 fs-7 d-none d-md-block d-lg-none d-xl-block">{{ trans('translation::base.fields.name.info') }}</div>
                                                </label>
                                                <input type="text" name="translation[name]" class="form-control" placeholder="{{ trans('translation::base.fields.name.placeholder') }}" value="{{ old('translation.name') }}">
                                                <div class="text-gray-600 fs-7 mt-2 d-md-none d-lg-block d-xl-none">{{ trans('translation::base.fields.name.info') }}</div>
                                                @error('translation.name')
                                                    <div class="form-errors text-danger fs-7 mt-2">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        @endif

                                        @if(isset($translation['fields']))
                                            @foreach($translation['fields'] as $field_key => $field_value)
                                                @if($field_key === 'name') @continue @endif
                                                <div>
                                                    <label class="form-label d-flex justify-content-between align-items-center">
                                                        <span>{{ trans($field_value['label']) }}</span>
                                                        <div class="text-gray-600 fs-7 d-none d-md-block d-lg-none d-xl-block">{{ trans($field_value['info']) }}</div>
                                                    </label>
                                                    @if($field_value['type'] === 'textarea')
                                                        <textarea name="translation[{{ $field_key }}]" class="form-control" placeholder="{{ trans($field_value['placeholder']) }}">{{ old('translation.' . $field_key) }}</textarea>
                                                    @endif
                                                    @if($field_value['type'] === 'text')
                                                        <input type="text" name="translation[{{ $field_key }}]" class="form-control" placeholder="{{ trans($field_value['placeholder']) }}" value="{{ old('translation.' . $field_key) }}">
                                                    @endif
                                                    <div class="text-gray-600 fs-7 mt-2 d-md-none d-lg-block d-xl-none">{{ trans($field_value['info']) }}</div>
                                                    @error('translation.' . $field_key)
                                                        <div class="form-errors text-danger fs-7 mt-2">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>

                                    @if(isset($translation['seo']) && $translation['seo'])
                                        <div class="tab-pane fade" id="tab_general_seo" role="tabpanel">
                                            <div class="mb-10">
                                                <label class="form-label d-flex justify-content-between align-items-center">
                                                    <span>{{ trans('translation::base.fields.meta_title.label') }}</span>
                                                    <div class="text-gray-600 fs-7 d-none d-md-block d-lg-none d-xl-block">{{ trans('translation::base.fields.meta_title.info') }}</div>
                                                </label>
                                                <input type="text" name="translation[meta_title]" class="form-control" placeholder="{{ trans('translation::base.fields.meta_title.placeholder') }}" value="{{ old('translation.meta_title') }}">
                                                <div class="text-gray-600 fs-7 mt-2 d-md-none d-lg-block d-xl-none">{{ trans('translation::base.fields.meta_title.info') }}</div>
                                                @error('translation.meta_title')
                                                    <div class="form-errors text-danger fs-7 mt-2">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="mb-10">
                                                <label class="form-label d-flex justify-content-between align-items-center">
                                                    <span>{{ trans('translation::base.fields.meta_description.label') }}</span>
                                                    <div class="text-gray-600 fs-7 mt-2 d-none d-md-block d-lg-none d-xl-block">{{ trans('translation::base.fields.meta_description.info') }}</div>
                                                </label>
                                                <input type="text" name="translation[meta_description]" class="form-control" placeholder="{{ trans('translation::base.fields.meta_description.placeholder') }}" value="{{ old('translation.meta_description') }}">
                                                <div class="text-gray-600 fs-7 mt-2 d-md- d-lg-block d-xl-none">{{ trans('translation::base.fields.meta_description.info') }}</div>
                                                @error('translation.meta_description')
                                                    <div class="form-errors text-danger fs-7 mt-2">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="mb-0">
                                                <label class="form-label d-flex justify-content-between align-items-center">
                                                    <span>{{ trans('translation::base.fields.meta_keywords.label') }}</span>
                                                    <div class="text-gray-600 fs-7 mt-2 d-none d-md-block d-lg-none d-xl-block">{!! trans('translation::base.fields.meta_keywords.info') !!}</div>
                                                </label>
                                                <input type="text" name="translation[meta_keywords]" class="form-control" placeholder="{{ trans('translation::base.fields.meta_keywords.placeholder') }}" value="{{ old('translation.meta_keywords') }}">
                                                <div class="text-gray-600 fs-7 mt-2 d-md-none d-lg-block d-xl-none">{!! trans('translation::base.fields.meta_keywords.info') !!}</div>
                                                @error('translation.meta_keywords')
                                                    <div class="form-errors text-danger fs-7 mt-2">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <!--end::General Name-->

                        <!--begin::Information-->
                        <div class="card card-flush py-4 mb-10">
                            <div class="card-header">
                                <div class="card-title">
                                    <span class="fs-5 fw-bold">{{ trans('package-core::base.cards.proprietary_info') }}</span>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="mb-10">
                                    <label class="form-label">{{ trans('category::base.form.fields.parent.title') }}</label>
                                    <select name="parent_id" class="form-select" data-control="select2">
                                        <option value="">{{ trans('package-core::base.select.none') }}</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" @if(old('parent_id') == $category->id) selected @endif>{{ $category->name_multiple }}</option>
                                        @endforeach
                                    </select>
                                    @error('parent_id')
                                        <div class="form-errors text-danger fs-7 mt-2">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="mb-0">
                                    <label class="form-label">{{ trans('category::base.form.fields.ordering.title') }}</label>
                                    <input type="number" name="ordering" class="form-control mb-2" placeholder="{{ trans('category::base.form.fields.ordering.placeholder') }}" value="{{ old('ordering') }}">
                                    @error('ordering')
                                        <div class="form-errors text-danger fs-7 mt-2">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <!--end::Information-->
                    </div>
                </div>
                <div class="tab-pane fade" id="tab_option">
                    ...
                </div>
                <div class="tab-pane fade" id="tab_layout">
                    <div class="d-flex flex-column gap-7 gap-lg-10">
                        <!--begin::Template settings-->
                        <div class="card card-flush py-4">
                            <div class="card-header">
                                <div class="card-title">
                                    <span class="fs-5 fw-bold">انتخاب تمپلیت</span>
                                </div>
                            </div>
                            <div class="card-body pt-0">
                                <select name="template" class="form-select" data-control="select2" data-placeholder="یک آیتم انتخاب کنید">
                                    <option value="default" selected="selected">پیش فرض</option>
                                    <option value="taraneh">ترانه</option>
                                </select>
                            </div>
                        </div>
                        <!--end::Template settings-->
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
