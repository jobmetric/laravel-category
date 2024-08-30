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
        "errors" => "Validation errors occurred.",
        "type_required" => "The type field is required.",
        "slug_in_type" => "The slug already exists in type (:type).",
        "category_exist" => "The category does not exist in type (:type).",
        "category_not_found" => "The category not found.",
    ],

    "messages" => [
        "created" => "The category was created successfully.",
        "updated" => "The category was updated successfully.",
        "deleted" => "The category was deleted successfully.",
        "attached" => "The category was attached successfully.",
        "multi_attached" => "The categories were attached successfully.",
        "used_in" => "The category used in ':count' places.",
    ],

    "exceptions" => [
        "category_not_found" => "The category with number :number was not found.",
        "category_is_disable" => "The category with number :number is disabled.",
        "category_used" => "The category ':name' is used in other places.",
        "cannot_make_parent_subset_own_child" => "Cannot make parent subset own child.",
        "model_category_contract_not_found" => "Model ':model' not implements 'JobMetric\Category\Contracts\CategoryContract' interface!",
        "category_collection_not_in_category_allow_types" => "The category collection :collection not in category allow types.",
        "invalid_category_type_in_collection" => "The type of collection entered is ':collection', and the ':collection' also accepts ':collectionType' category type, but the category type you have given is ':baseType' and it is not acceptable.",
        "category_type_not_match" => "The category type ':type' does not match.",
    ],

];
