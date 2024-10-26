@extends('panelio::layout.layout')

@section('body')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="ki-duotone ki-category fs-1 me-3">
                    <span class="path1"></span>
                    <span class="path2"></span>
                    <span class="path3"></span>
                    <span class="path4"></span>
                </i>
                <span>لیست دسته بندی محصولات</span>
            </h3>
            <div class="card-toolbar">
                <button type="button" class="btn btn-sm btn-light-facebook me-3">
                    <i class="la la-search fs-2 position-absolute"></i>
                    <span class="d-none d-md-inline ps-9">جستجو</span>
                </button>
                <button type="button" class="btn btn-sm btn-icon btn-light-facebook me-3 px-3" id="button-delete-filter" data-bs-toggle="tooltip" data-bs-placement="bottom" title="حذف فیلترها">
                    <i class="la la-remove fs-2 p-0"></i>
                </button>
                <button type="button" class="btn btn-sm btn-light-info">
                    <i class="la la-filter fs-2 position-absolute"></i>
                    <span class="d-none d-md-inline ps-9">فیلتر</span>
                </button>
            </div>
        </div>
        <div class="card-body py-5">
            <div class="border-bottom mb-5">
                <div class="row align-items-center">
                    <div class="col-md-3">
                        <div class="mb-5">
                            <label class="form-label">نام</label>
                            <input type="text" name="translation[name]" class="form-control filter-list" id="filter-name" placeholder="نام را وارد کنید" value="" autocomplete="off">
                        </div>
                    </div>
                </div>
            </div>
            <form method="post" action="" id="form">
                @csrf
                <table class="table table-bordered table-striped table-hover table-check" id="datatable">
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
                </table>
            </form>
        </div>
    </div>
@endsection
