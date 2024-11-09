<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Base Category Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during Category for
    | various messages that we need to display to the user.
    |
    */

    "validation" => [
        "errors" => "خطای اعتبارسنجی رخ داده است.",
        "type_required" => "فیلد نوع الزامی است.",
        "slug_in_type" => "نام مستعار در نوع (:type) قبلا وجود دارد.",
        "category_exist" => "دسته بندی در نوع (:type) وجود ندارد.",
        "category_not_found" => "دسته بندی یافت نشد.",
    ],

    "messages" => [
        "created" => "دسته بندی با موفقیت ایجاد شد.",
        "updated" => "دسته بندی با موفقیت به روز شد.",
        "deleted" => "دسته بندی با موفقیت حذف شد.",
        "attached" => "دسته بندی با موفقیت متصل شد.",
        "multi_attached" => "دسته بندی ها با موفقیت متصل شدند.",
        "used_in" => "دسته بندی در ':count' مکان استفاده شده است.",
        "deleted_items" => "{1} یک مورد :taxonomy با موفقیت حذف شد.|[2,*] :count مورد :taxonomy با موفقیت حذف شدند.",
        "status" => [
            "enable" => "{1} یک مورد :taxonomy فعال شد.|[2,*] :count مورد :taxonomy فعال شدند.",
            "disable" => "{1} یک مورد :taxonomy غیرفعال شد.|[2,*] :count مورد :taxonomy غیرفعال شدند.",
        ],
    ],

    "exceptions" => [
        "category_not_found" => "دسته بندی با شماره :number یافت نشد.",
        "category_is_disable" => "دسته بندی با شماره :number غیرفعال است.",
        "category_used" => "دسته بندی ':name' در مکان های دیگر استفاده شده است.",
        "cannot_make_parent_subset_own_child" => "نمی توان پدر زیرمجموعه خود را ایجاد کرد.",
        "model_category_contract_not_found" => "مدل ':model' رابط 'JobMetric\Category\Contracts\CategoryContract' را پیاده سازی نمی کند!",
        "category_collection_not_in_category_allow_types" => "مجموعه دسته بندی :collection در انواع دسته بندی مجاز نیست.",
        "invalid_category_type_in_collection" => "نوع مجموعه وارد شده ':collection' است، و ':collection' همچنین نوع دسته بندی ':collectionType' را قبول می کند، اما نوع دسته بندی داده شده ':baseType' است و قابل قبول نیست.",
        "category_type_not_match" => "نوع دسته بندی ':type' مطابقت ندارد.",
    ],

    "list" => [
        "filters" => [
            "name" => [
                "title" => "نام",
                "placeholder" => "نام را وارد کنید.",
            ],
            "status" => [
                "title" => "وضعیت",
            ],
        ],
        "columns" => [
            "name" => "نام",
            "status" => "وضعیت",
            "ordering" => "ترتیب",
            "action" => "عملیات",
            "created_at" => "تاریخ ایجاد",
            "updated_at" => "تاریخ به روزرسانی",
        ],
    ],

    "form" => [
        "create" => [
            "title" => "ایجاد :type",
        ],
        "edit" => [
            "title" => "ویرایش :type :name",
        ],
        "media" => [
            "base" => [
                "title" => "عکس اصلی",
            ],
            "gallery" => [
                "title" => "تصاویر بیشتر",
            ],
        ],
        "fields" => [
            "parent" => [
                "title" => "والد"
            ],
            "ordering" => [
                "title" => "ترتیب",
                "placeholder" => "ترتیب را وارد کنید.",
            ],
        ],
    ]

];
