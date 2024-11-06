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
                        <x-file-single name="{{ trans('category::base.form.media.base.title') }}" collection="base" mime-types="image" value="{{ old('media.base', isset($media_values['base']) ? implode(',', $media_values['base']) : '') }}" />
                    @endif

                    @foreach($media as $media_collection => $media_item)
                        @if($media_item['multiple'])
                            <x-file-multiple name="{{ trans('category::base.form.media.' . $media_collection . '.title') }}" collection="{{ $media_collection }}" mime-types="{{ implode(',', $media_item['mime_types']) }}" value="{{ implode(',', old('media.' . $media_collection, $media_values[$media_collection] ?? [])) }}" />
                        @else
                            <x-file-single name="{{ trans('category::base.form.media.' . $media_collection . '.title') }}" collection="{{ $media_collection }}" mime-types="{{ implode(',', $media_item['mime_types']) }}" value="{{ old('media.' . $media_collection, isset($media_values[$media_collection]) ? implode(',', $media_values[$media_collection]) : '') }}" />
                        @endif
                    @endforeach
                </x-file-manager>
                <!--end::image-->
            @endif

            @if($has_url)
                <x-url-slug value="{{ old('slug', $slug ?? null) }}" />
            @endif

            <x-boolean-status value="{{ old('status', $category->status ?? true) }}" />
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
                        @php
                            $translation_values = [];
                            $translation_values['name'] = old('translation.name');
                            foreach($translation['fields'] ?? [] as $translation_key => $translation_value) {
                                $translation_values[$translation_key] = old('translation.' . $translation_key);
                            }
                            if (isset($translation['seo']) && $translation['seo']) {
                                $translation_values['meta_title'] = old('translation.meta_title');
                                $translation_values['meta_description'] = old('translation.meta_description');
                                $translation_values['meta_keywords'] = old('translation.meta_keywords');
                            }
                        @endphp
                        <x-translation-card :items="$translation" :values="$translation_values" />

                        <!--begin::Information-->
                        <div class="card card-flush py-4 @empty($metadata) mb-10 @endif">
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
                                        @foreach($categories as $category_item)
                                            <option value="{{ $category_item->id }}" @if(old('parent_id', $category->parent_id ?? null) == $category_item->id) selected @endif>{{ $category_item->name_multiple }}</option>
                                        @endforeach
                                    </select>
                                    @error('parent_id')
                                        <div class="form-errors text-danger fs-7 mt-2">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="mb-0">
                                    <label class="form-label">{{ trans('category::base.form.fields.ordering.title') }}</label>
                                    <input type="number" name="ordering" class="form-control mb-2" placeholder="{{ trans('category::base.form.fields.ordering.placeholder') }}" value="{{ old('ordering', $category->ordering ?? null) }}">
                                    @error('ordering')
                                        <div class="form-errors text-danger fs-7 mt-2">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <!--end::Information-->

                        @empty(!$metadata)
                            @php
                                $metadata_values = [];
                                foreach($metadata as $metadata_key => $metadata_value) {
                                    $metadata_values[$metadata_key] = old('metadata.' . $metadata_key, $meta_values[$metadata_key] ?? null);
                                }
                            @endphp
                            <x-metadata-card :items="$metadata" :values="$metadata_values" />
                        @endif
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
