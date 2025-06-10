<?php

namespace App\Helpers;

use App\Http\Controllers\Admin\UserCrudController;
use App\Models\Admin;
use App\Models\Company;
use App\Models\Customers;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AdminCustomerHelper
{
    public static function applyAdminCompanyFilter(Builder $query, $adminId, $companyColumn = 'id'): Builder
    {
        if (self::isSuperAdmin($adminId)) {
            return $query;
        }

        return $query->whereIn($companyColumn, function ($subQuery) use ($adminId) {
            $subQuery->select('company_id')
                ->from('user_company')
                ->where('user_id', $adminId);
        });
    }

    protected static function isSuperAdmin($adminId): bool
    {
        $admin = User::find($adminId);
        return $admin && $admin->hasRole('Super Admin');
    }

    public static function applyAdminOrderFilter(Builder $query, $adminId, $companyColumn = 'company_id'): Builder
    {
        if (self::isSuperAdmin($adminId)) {
            return $query;
        }

        $companyIds = DB::table('user_company')
            ->where('user_id', $adminId)
            ->pluck('company_id');

        return $query->whereIn($companyColumn, $companyIds);
    }

    public static function applyAdminModelFilter(Builder $query, $adminId, $companyColumn = 'company_id'): Builder
    {
        if (self::isSuperAdmin($adminId)) {
            return $query;
        }

        $companyIds = DB::table('admin_customer as ac')
            ->join('customers as c', 'ac.customer_id', '=', 'c.id')
            ->join('company as co', 'c.id', '=', 'co.customer_id')
            ->where('ac.admin_id', $adminId)
            ->pluck('co.id');

        return $query->whereIn($companyColumn, $companyIds);
    }

    public static function getCompanyOptions($adminId,$type='filter')
    {
        $admin = User::find($adminId);
        if ($admin && $admin->hasRole('Super Admin')) {
            // Super admin can access all companies
            if($type == 'create') {
                return Company::all();
            } else {
                return Company::all()->pluck('name', 'id')->toArray();
            }
        } else {
            // Get company IDs for the assigned customers
            $companyIds = DB::table('user_company as uc')
                ->join('companies as c', 'uc.company_id', '=', 'c.id')
                ->where('uc.user_id', $adminId)
                ->pluck('c.id');

            return $type == 'create' ? Company::whereIn('id', $companyIds)->get() : Company::whereIn('id', $companyIds)->pluck('name', 'id')->toArray();
        }
    }

    public static function getCustomerOptions($adminId,$type='filter')
    {
        $admin = Admin::find($adminId);
        if ($admin && $admin->hasRole('super-admin')) {
            // Super admin can access all companies
            if($type == 'create') {
                return Customers::all();
            } else {
                return Customers::all()->pluck('full_name', 'id')->toArray();
            }
        } else {
            // Get company IDs for the assigned customers
            $customerIds = DB::table('admin_customer')
                ->where('admin_id', $adminId)
                ->pluck('customer_id');

            return $type == 'create' ? Customers::whereIn('id', $customerIds)->get() : Customers::whereIn('id', $customerIds)->pluck('name', 'id')->toArray();
        }
    }

    public static function getCompanyIdsByCustomer($adminId): Collection
    {
        $admin = Admin::find($adminId);
        if ($admin && $admin->hasRole('super-admin')) {
            $companyIds = Company::all()->pluck('id', 'id');
        } else {
            $companyIds = DB::table('admin_customer as ac')
                ->join('customers as c', 'ac.customer_id', '=', 'c.id')
                ->join('company as co', 'c.id', '=', 'co.customer_id')
                ->where('ac.admin_id', $adminId)
                ->pluck('co.id');
        }

        return $companyIds;
    }
}
