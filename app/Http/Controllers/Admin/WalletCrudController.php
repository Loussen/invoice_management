<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\WalletRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class WalletCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class WalletCrudController extends CrudController
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
        CRUD::setModel(\App\Models\Wallet::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/wallet');
        CRUD::setEntityNameStrings('wallet', 'wallets');

        if (!backpack_user()->can('wallet list')) {
            CRUD::denyAccess(['list', 'show']);
        }

        if (!backpack_user()->can('wallet create')) {
            CRUD::denyAccess(['create']);
        }

        if (!backpack_user()->can('wallet update')) {
            CRUD::denyAccess(['update']);
        }

        if (!backpack_user()->can('wallet delete')) {
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
        CRUD::addColumn([
            'name'        => 'user_id',
            'type'        => 'select2',
            'allows_null' => true,
            'attribute'   => 'full_name',
        ]);
        CRUD::addColumn([
            'name'        => 'coin_id',
            'type'        => 'select2',
            'allows_null' => true,
            'attribute'   => 'full_name',
        ]);
        CRUD::column('address');

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
        CRUD::setValidation(WalletRequest::class);
        CRUD::addField([
            'name'        => 'user_id',
            'type'        => 'select2',
            'allows_null' => true,
            'attribute'   => 'full_name',
            'wrapper'     => ['class' => 'form-group col-md-6']
        ]);
        CRUD::addField([
            'name'        => 'coin_id',
            'type'        => 'select2',
            'allows_null' => true,
            'attribute'   => 'full_name',
            'wrapper'     => ['class' => 'form-group col-md-6']
        ]);
        CRUD::field('address');

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

    protected function autoSetupShowOperation()
    {
        $this->setupListOperation();
    }
}
