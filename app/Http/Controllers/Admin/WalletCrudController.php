<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\AdminCustomerHelper;
use App\Http\Requests\WalletRequest;
use App\Models\Coin;
use App\Models\User;
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
        if (!backpack_user()->hasRole('Super Admin')) {
            CRUD::addClause('where', 'user_id', backpack_user()->id);
        }

        if (backpack_user()->hasRole('Super Admin')) {
            CRUD::addColumn([
                'name' => 'user_id',
                'type' => 'select2',
                'allows_null' => true,
                'attribute' => 'full_name',
            ]);
        }
        CRUD::addColumn([
            'name'        => 'coin_id',
            'type'        => 'select2',
            'allows_null' => true,
            'attribute'   => 'full_name',
        ]);
        CRUD::column('address');
        CRUD::addColumn([
            'name' => 'status',
            'type' => 'select_from_array',
            'options' => ['pending' => 'Pending', 'approved' => 'Approved'],
        ]);

        $this->addCustomCrudFilters();

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
        if (backpack_user()->hasRole('Super Admin')) {
            CRUD::addField([
                'name'        => 'user_id',
                'type'        => 'select2',
                'entity'      => 'user',
                'model'       => \App\Models\User::class,
                'attribute'   => 'full_name', // və ya 'name'
                'allows_null' => false,
                'wrapper'     => ['class' => 'form-group col-md-6'],
            ]);
        } else {
            // adi istifadəçidə user_id gizlədilir və dəyər avtomatik verilir
            CRUD::addField([
                'name'  => 'user_id',
                'type'  => 'hidden',
                'value' => backpack_user()->id,
            ]);
        }
        CRUD::addField([
            'name'        => 'coin_id',
            'type'        => 'select2',
            'allows_null' => true,
            'attribute'   => 'full_name',
            'wrapper'     => ['class' => 'form-group col-md-6']
        ]);
        CRUD::field('address')->wrapper(['class' => 'form-group col-md-6']);

        if (backpack_user()->hasRole('Super Admin')) {
            CRUD::addField([
                'name' => 'status',
                'type' => 'select_from_array',
                'options' => ['pending' => 'Pending', 'approved' => 'Approved'],
                'allows_null' => true,
                'wrapper' => ['class' => 'form-group col-md-6']
            ]);
        } else {
            CRUD::addField([
                'name'  => 'status',
                'type'  => 'hidden',
                'value' => 'pending',
            ]);
        }

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

    protected function addCustomCrudFilters(): void
    {
        $adminId = backpack_user()->id;

        CRUD::addFilter([
            'name' => 'user_id',
            'type' => 'select2',
            'label' => 'User',
        ],
            function () {
                return User::pluck('name','id')->toArray();
            },
            function ($value) {
                $this->crud->addClause('where', 'user_id', $value);
            }
        );

        CRUD::addFilter([
            'name' => 'coin_id',
            'type' => 'select2',
            'label' => 'Coin',
        ],
            function () {
                return Coin::pluck('name','id')->toArray();
            },
            function ($value) {
                $this->crud->addClause('where', 'coin_id', $value);
            }
        );

        CRUD::addFilter([
            'name'  => 'status',
            'type'  => 'select2',
            'label' => 'Status'
        ], function () {
            return ['pending' => 'Pending', 'approved' => 'Approved'];
        }, function ($value) { // if the filter is active
            $this->crud->addClause('where', 'status', $value);
        });
    }
}
