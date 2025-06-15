<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\CurrencyRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class CurrencyCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class CurrencyCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\Currency::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/currency');
        CRUD::setEntityNameStrings('currency', 'currency');

        if (!backpack_user()->can('currency list')) {
            CRUD::denyAccess(['list', 'show']);
        }

        if (!backpack_user()->can('currency create')) {
            CRUD::denyAccess(['create']);
        }

        if (!backpack_user()->can('currency update')) {
            CRUD::denyAccess(['update']);
        }

        if (!backpack_user()->can('currency delete')) {
            CRUD::denyAccess(['delete']);
        }
    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        CRUD::column('symbol');
        CRUD::column('code');
        CRUD::column('name');

        /**
         * Columns can be defined using the fluent syntax:
         * - CRUD::column('price')->type('number');
         */
    }

    /**
     * Define what happens when the Create operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(CurrencyRequest::class);
        CRUD::field('code')->wrapper(['class' => 'form-group col-md-4']);
        CRUD::field('name')->wrapper(['class' => 'form-group col-md-4']);
        CRUD::field('symbol')->wrapper(['class' => 'form-group col-md-4']);

        /**
         * Fields can be defined using the fluent syntax:
         * - CRUD::field('price')->type('number');
         */
    }

    /**
     * Define what happens when the Update operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-update
     * @return void
     */
    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }
}
