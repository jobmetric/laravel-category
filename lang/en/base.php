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

    'validation' => [
        'errors' => 'Validation errors occurred.',
        'type_required' => 'The type field is required.',
        'slug_in_type' => 'The slug already exists in type (:type).',
        'category_exist' => 'The category does not exist in type (:type).',
        'category_not_found' => 'The category not found.',
    ],

    'messages' => [
        'created' => 'The category was created successfully.',
        'updated' => 'The category was updated successfully.',
        'deleted' => 'The category was deleted successfully.',
    ],

    'exceptions' => [
        'category_not_found' => 'The category with number :number was not found.',
        'category_used' => 'The category with number :number is used in other places.',
        'cannot_make_parent_subset_own_child' => 'Cannot make parent subset own child.',
    ],

];
