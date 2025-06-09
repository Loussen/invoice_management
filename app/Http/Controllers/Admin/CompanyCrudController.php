<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\AdminCustomerHelper;
use App\Http\Requests\CompanyRequest;
use App\Models\Currency;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Backpack\Pro\Http\Controllers\Operations\DropzoneOperation;

/**
 * Class CompanyCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class CompanyCrudController extends CrudController
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
        CRUD::setModel(\App\Models\Company::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/company');
        CRUD::setEntityNameStrings('company', 'companies');

        if (!backpack_user()->can('company list')) {
            CRUD::denyAccess(['list', 'show']);
        }

        if (!backpack_user()->can('company create')) {
            CRUD::denyAccess(['create']);
        }

        if (!backpack_user()->can('company update')) {
            CRUD::denyAccess(['update']);
        }

        if (!backpack_user()->can('company delete')) {
            CRUD::denyAccess(['delete']);
        }

        $adminId = backpack_user()->id;

        AdminCustomerHelper::applyAdminCustomerFilter($this->crud->query, $adminId);
    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        CRUD::column('name');
        CRUD::column('iban')->label('IBAN');
        CRUD::column('address');
        CRUD::column('swift')->label('SWIFT / BIC');
        CRUD::column('bank_name');
        CRUD::column('country');
        CRUD::addColumn([
            'name' => 'document',
            'label' => 'Documents',
            'type' => 'dropzone',
            'disk' => 'company_document',
            'withFiles'    => true,
        ]);
        CRUD::column('monthly_limit')->type('number');
        CRUD::column('daily_limit')->type('number');
        CRUD::column('max_limit')->label('1 Transaction Max Limit')->type('number');
        CRUD::column('bank_address');
        CRUD::addColumn([
            'name'        => 'currency_id',
            'type'        => 'select2',
            'allows_null' => true,
            'attribute'   => 'full_name',
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
        CRUD::setValidation(CompanyRequest::class);
        CRUD::field('name')->wrapper(['class' => 'form-group col-md-3']);
        CRUD::field('iban')->label('IBAN')->wrapper(['class' => 'form-group col-md-3']);
        CRUD::field('address')->wrapper(['class' => 'form-group col-md-6']);
        CRUD::field('swift')->label('SWIFT / BIC')->wrapper(['class' => 'form-group col-md-4']);
        CRUD::field('bank_name')->wrapper(['class' => 'form-group col-md-4']);
        CRUD::field('country')->wrapper(['class' => 'form-group col-md-4']);
        CRUD::addField([
            'name' => 'document',
            'label' => 'Documents',
            'type' => 'dropzone',
            'disk' => 'company_document',
            'withFiles'    => true,
        ]);
        CRUD::field('monthly_limit')->type('number')->wrapper(['class' => 'form-group col-md-4']);
        CRUD::field('daily_limit')->type('number')->wrapper(['class' => 'form-group col-md-4']);
        CRUD::field('max_limit')->label('1 Transaction Max Limit')->type('number')->wrapper(['class' => 'form-group col-md-4']);
        CRUD::field('bank_address')->wrapper(['class' => 'form-group col-md-6']);
        CRUD::addField([
            'name'        => 'currency_id',
            'type'        => 'select2',
            'allows_null' => true,
            'attribute'   => 'full_name',
            'wrapper'     => ['class' => 'form-group col-md-6']
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
        $this->setupCreateOperation();
    }

    protected function autoSetupShowOperation()
    {
        $this->setupListOperation();
    }

    protected function addCustomCrudFilters(): void
    {
        $adminId = backpack_user()->id;

        CRUD::addFilter(
            [
                'type' => 'text',
                'label' => 'Name',
                'name' => 'name',
            ],
            false,
            function ($value) {
                CRUD::addClause('where', 'name', 'LIKE', "$value%");
            }
        );

        CRUD::addFilter(
            [
                'type' => 'text',
                'label' => 'IBAN',
                'name' => 'iban',
            ],
            false,
            function ($value) {
                CRUD::addClause('where', 'iban', 'LIKE', "$value%");
            }
        );

        CRUD::addFilter(
            [
                'type' => 'text',
                'label' => 'SWIFT/BIC',
                'name' => 'swift',
            ],
            false,
            function ($value) {
                CRUD::addClause('where', 'iban', 'LIKE', "$value%");
            }
        );

        CRUD::addFilter(
            [
                'type' => 'text',
                'label' => 'Bank name',
                'name' => 'bank_name',
            ],
            false,
            function ($value) {
                CRUD::addClause('where', 'bank_name', 'LIKE', "$value%");
            }
        );

        CRUD::addFilter(
            [
                'type' => 'text',
                'label' => 'Country',
                'name' => 'country',
            ],
            false,
            function ($value) {
                CRUD::addClause('where', 'country', 'LIKE', "$value%");
            }
        );

        CRUD::addFilter([
            'name' => 'currency',
            'type' => 'select2',
            'label' => 'Currency',
        ],
            function () {
                return Currency::pluck('name', 'id')->toArray();
            },
            function ($value) {
                $this->crud->addClause('where', 'currency_id', $value);
            }
        );

        CRUD::addFilter(
            [
                'name'       => 'monthly_limit',
                'type'       => 'range',
                'label_from' => 'min',
                'label_to'   => 'max',
            ],
            false,
            function ($value) {
                $range = json_decode($value);
                if ($range->from) {
                    CRUD::addClause('where', 'monthly_limit', '>=',  (float) $range->from);
                }
                if($range->to) {
                    CRUD::addClause('where', 'monthly_limit', '<=', (float) $range->to);
                }
            }
        );

        CRUD::addFilter(
            [
                'name'       => 'daily_limit',
                'type'       => 'range',
                'label_from' => 'min',
                'label_to'   => 'max',
            ],
            false,
            function ($value) {
                $range = json_decode($value);
                if ($range->from) {
                    CRUD::addClause('where', 'daily_limit', '>=',  (float) $range->from);
                }
                if($range->to) {
                    CRUD::addClause('where', 'daily_limit', '<=', (float) $range->to);
                }
            }
        );

        CRUD::addFilter(
            [
                'name'       => 'max_limit',
                'type'       => 'range',
                'label_from' => 'min',
                'label_to'   => 'max',
            ],
            false,
            function ($value) {
                $range = json_decode($value);
                if ($range->from) {
                    CRUD::addClause('where', 'max_limit', '>=',  (float) $range->from);
                }
                if($range->to) {
                    CRUD::addClause('where', 'max_limit', '<=', (float) $range->to);
                }
            }
        );
    }
}
