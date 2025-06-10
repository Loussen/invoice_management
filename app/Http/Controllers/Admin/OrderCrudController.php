<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\AdminCustomerHelper;
use App\Http\Requests\OrderRequest;
use App\Models\Company;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Backpack\Pro\Http\Controllers\Operations\DropzoneOperation;

/**
 * Class OrderCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class OrderCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    use DropzoneOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\Order::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/order');
        CRUD::setEntityNameStrings('order', 'orders');

        if (!backpack_user()->can('order list')) {
            CRUD::denyAccess(['list', 'show']);
        }

        if (!backpack_user()->can('order create')) {
            CRUD::denyAccess(['create']);
        }

        if (!backpack_user()->can('order update')) {
            CRUD::denyAccess(['update']);
        }

        if (!backpack_user()->can('order delete')) {
            CRUD::denyAccess(['delete']);
        }

        $adminId = backpack_user()->id;

        AdminCustomerHelper::applyAdminOrderFilter($this->crud->query, $adminId);
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
            'name'        => 'company_id',
            'type'        => 'select2',
            'allows_null' => true,
        ]);
        CRUD::addColumn([
            'name' => 'status',
            'type' => 'select_from_array',
            'options' => ['pending' => 'Pending', 'completed' => 'Completed', 'refund' => 'Refund', 'reject' => 'Reject'],
            'allows_null' => true,
        ]);
        CRUD::column('transaction_number');
        CRUD::column('amount_with_currency');
        CRUD::column('payeer_name');
        CRUD::addColumn([
            'name' => 'detail_document',
            'type' => 'dropzone',
            'disk' => 'order_detail_document',
            'withFiles'    => true,
        ]);
        CRUD::column('id_passport');
        CRUD::addColumn([
            'name' => 'receipt',
            'type' => 'dropzone',
            'disk' => 'order_user_receipt',
            'withFiles'    => true,
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
        CRUD::setValidation(OrderRequest::class);
        CRUD::addField([
            'name'        => 'company_id',
            'type'        => 'select2',
            'allows_null' => true,
            'wrapper'     => ['class' => 'form-group col-md-2']
        ]);
        CRUD::addField([
            'name' => 'status',
            'type' => 'select_from_array',
            'options' => ['pending' => 'Pending', 'completed' => 'Completed', 'refund' => 'Refund', 'reject' => 'Reject'],
            'allows_null' => true,
            'wrapper' => ['class' => 'form-group col-md-2']
        ]);
        CRUD::field('transaction_number')->wrapper(['class' => 'form-group col-md-2']);
        CRUD::field('amount')->type('number')->wrapper(['class' => 'form-group col-md-2']);
        CRUD::addField([
            'name'        => 'currency_id',
            'type'        => 'select2',
            'allows_null' => true,
            'attribute'   => 'full_name',
            'wrapper'     => ['class' => 'form-group col-md-2']
        ]);
        CRUD::field('payeer_name')->wrapper(['class' => 'form-group col-md-2']);
        CRUD::addField([
            'name' => 'detail_document',
            'type' => 'dropzone',
            'disk' => 'order_detail_document',
            'withFiles'    => true,
        ]);

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
        if(backpack_user()->hasRole('Super Admin')) {
            $this->setupCreateOperation();
        } else {
            CRUD::field('id_passport')->wrapper(['class' => 'form-group col-md-6']);
            CRUD::addField([
                'name' => 'receipt',
                'type' => 'dropzone',
                'disk' => 'order_user_receipt',
                'withFiles'    => true,
            ]);
        }
    }

    protected function autoSetupShowOperation()
    {
        $this->setupListOperation();
    }

    protected function addCustomCrudFilters(): void
    {
        $adminId = backpack_user()->id;

        CRUD::addFilter([
            'name' => 'company_id',
            'type' => 'select2',
            'label' => 'Company',
        ],
            function () use ($adminId) {
                return AdminCustomerHelper::getCompanyOptions($adminId);
            },
            function ($value) {
                $this->crud->addClause('where', 'company_id', $value);
            }
        );

        CRUD::addFilter(
            [
                'name'       => 'amount',
                'type'       => 'range',
                'label_from' => 'min',
                'label_to'   => 'max',
            ],
            false,
            function ($value) {
                $range = json_decode($value);
                if ($range->from) {
                    CRUD::addClause('where', 'amount', '>=',  (float) $range->from);
                }
                if($range->to) {
                    CRUD::addClause('where', 'amount', '<=', (float) $range->to);
                }
            }
        );

        CRUD::addFilter([
            'name'  => 'status',
            'type'  => 'select2',
            'label' => 'Status'
        ], function () {
            return ['pending' => 'Pending', 'completed' => 'Completed', 'refund' => 'Refund', 'reject' => 'Reject'];
        }, function ($value) { // if the filter is active
            $this->crud->addClause('where', 'status', $value);
        });

        CRUD::addFilter(
            [
                'type' => 'text',
                'label' => 'Transaction Number',
                'name' => 'transaction_number',
            ],
            false,
            function ($value) {
                CRUD::addClause('where', 'transaction_number', 'LIKE', "$value%");
            }
        );

        CRUD::addFilter(
            [
                'type' => 'text',
                'name' => 'payeer_name',
            ],
            false,
            function ($value) {
                CRUD::addClause('where', 'payeer_name', 'LIKE', "$value%");
            }
        );
    }
}
