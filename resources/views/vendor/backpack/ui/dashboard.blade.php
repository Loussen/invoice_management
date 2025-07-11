@extends(backpack_view('blank'))

@php
    // ---------------------
    // JUMBOTRON widget demo
    // ---------------------
    // Widget::add([
 //        'type'        => 'jumbotron',
 //        'name' 		  => 'jumbotron',
 //        'wrapperClass'=> 'shadow-xs',
 //        'heading'     => trans('backpack::base.welcome'),
 //        'content'     => trans('backpack::base.use_sidebar'),
 //        'button_link' => backpack_url('logout'),
 //        'button_text' => trans('backpack::base.logout'),
 //    ])->to('before_content')->makeFirst();

    // -------------------------
    // FLUENT SYNTAX for widgets
    // -------------------------
    // Using the progress_white widget
    //
    // Obviously, you should NOT do any big queries directly in the view.
    // In fact, it can be argued that you shouldn't add Widgets from blade files when you
    // need them to show information from the DB.
    //
    // But you do whatever you think it's best. Who am I, your mom?
    $adminId = backpack_user()->id;

    $admin = \App\Models\User::find($adminId);
    if($admin) {
        if($admin->hasRole('Super Admin')) {
            $orderCount = \App\Models\Order::count();

            $orderAmountSum = \App\Models\Order::where('status', 'completed')->sum('amount');
            $orderAmountSum = number_format($orderAmountSum);

            $orderAmountRejectSum = \App\Models\Order::where('status', 'reject')->sum('amount');
            $orderAmountRejectSum = number_format($orderAmountRejectSum);
            $orderAmountRejectCount = \App\Models\Order::where('status', 'reject')->count();

            $orderAmountPendingSum = \App\Models\Order::where('status', 'pending')->sum('amount');
            $orderAmountPendingSum = number_format($orderAmountPendingSum);
            $orderAmountPendingCount = \App\Models\Order::where('status', 'pending')->count();

            $companyCount = \App\Models\Company::count();
            $walletCount = \App\Models\Wallet::count();
            $orderAmountCompletedCount = \App\Models\Order::where('status', 'completed')->count();
            Widget::add()->to('before_content')->type('div')->class('row mt-4')->content([
                // notice we use Widget::make() to add widgets as content (not in a group)
                Widget::make()
                    ->type('progress')
                    ->class('card mb-4')
                    ->statusBorder('start') // start|top|bottom
                    ->accentColor('success') // primary|secondary|warning|danger|info
                    ->ribbon(['top', 'la-first-order']) // ['top|right|bottom']
                    ->progressClass('progress-bar')
                    ->value($orderCount.' orders')
                    ->description('Orders count')
                    ->hint('<a href='.backpack_url('order').'>Order List</a>'),
                // alternatively, to use widgets as content, we can use the same add() method,
                // but we need to use onlyHere() or remove() at the end
                Widget::make()
                    ->type('progress')
                    ->class('card mb-4')
                    ->statusBorder('start') // start|top|bottom
                    ->accentColor('info') // primary|secondary|warning|danger|info
                    ->ribbon(['top', 'la-first-order']) // ['top|right|bottom']
                    ->progressClass('progress-bar')
                    ->value($companyCount.' company')
                    ->description('Company count')
                    ->hint('<a href='.backpack_url('company').'>Company List</a>'),
                // alternatively, you can just push the widget to a "hidden" group
                Widget::make()
                    ->type('progress')
                    ->class('card mb-4')
                    ->statusBorder('start') // start|top|bottom
                    ->accentColor('danger') // primary|secondary|warning|danger|info
                    ->ribbon(['top', 'la-first-order']) // ['top|right|bottom']
                    ->progressClass('progress-bar')
                    ->value($orderAmountSum)
                    ->description('Wallet Balance')
                    ->hint('Completed order count: '.$orderAmountCompletedCount),
                Widget::make()
                    ->type('progress')
                    ->class('card mb-4')
                    ->statusBorder('start') // start|top|bottom
                    ->accentColor('primary') // primary|secondary|warning|danger|info
                    ->ribbon(['top', 'la-first-order']) // ['top|right|bottom']
                    ->progressClass('progress-bar')
                    ->value($walletCount)
                    ->description('Wallet Count')
                    ->hint('<a href='.backpack_url('wallet').'>Wallet List</a>'),
                Widget::make()
                    ->type('progress')
                    ->class('card mb-4')
                    ->statusBorder('start') // start|top|bottom
                    ->accentColor('primary') // primary|secondary|warning|danger|info
                    ->ribbon(['top', 'la-first-order']) // ['top|right|bottom']
                    ->progressClass('progress-bar')
                    ->value($orderAmountRejectSum)
                    ->description('Rejected order')
                    ->hint('Rejected order count: '.$orderAmountRejectCount),
                Widget::make()
                    ->type('progress')
                    ->class('card mb-4')
                    ->statusBorder('start') // start|top|bottom
                    ->accentColor('primary') // primary|secondary|warning|danger|info
                    ->ribbon(['top', 'la-first-order']) // ['top|right|bottom']
                    ->progressClass('progress-bar')
                    ->value($orderAmountPendingSum)
                    ->description('Pending order')
                    ->hint('Pending order count: '.$orderAmountPendingCount)
            ]);
        } else {
            $companyIds = $admin->companies()->pluck('companies.id');

            if($companyIds->isNotEmpty()) {
                $orderCount = \App\Models\Order::whereIn('company_id', $companyIds)->count();

                $orderAmountSum = \App\Models\Order::where('status', 'completed')->whereIn('company_id', $companyIds)->sum('amount');
                $commissionRate = $admin->commission / 100;
                $orderAmountSumAfterCommission = $orderAmountSum * (1 - $commissionRate);
                $orderAmountSumAfterCommission = number_format($orderAmountSumAfterCommission);

                $orderAmountRejectSum = \App\Models\Order::where('status', 'reject')->whereIn('company_id', $companyIds)->sum('amount');
                $orderAmountRejectSum = number_format($orderAmountRejectSum);
                $orderAmountRejectCount = \App\Models\Order::where('status', 'reject')->whereIn('company_id', $companyIds)->count();

                $orderAmountPendingSum = \App\Models\Order::where('status', 'pending')->whereIn('company_id', $companyIds)->sum('amount');
                $orderAmountPendingSum = number_format($orderAmountPendingSum);
                $orderAmountPendingCount = \App\Models\Order::where('status', 'pending')->whereIn('company_id', $companyIds)->count();

                $companyCount = $companyIds->count();
                $walletCount = \App\Models\Wallet::where('user_id',$adminId)->count();
                $orderAmountCompletedCount = \App\Models\Order::where('status', 'completed')->whereIn('company_id', $companyIds)->count();

                Widget::add()->to('before_content')->type('div')->class('row mt-4')->content([
                    // notice we use Widget::make() to add widgets as content (not in a group)
                    Widget::make()
                        ->type('progress')
                        ->class('card mb-4')
                        ->statusBorder('start') // start|top|bottom
                        ->accentColor('success') // primary|secondary|warning|danger|info
                        ->ribbon(['top', 'la-first-order']) // ['top|right|bottom']
                        ->progressClass('progress-bar')
                        ->value($orderCount.' orders')
                        ->description('Orders count')
                        ->hint('<a href='.backpack_url('order').'>Order List</a>'),
                    // alternatively, to use widgets as content, we can use the same add() method,
                    // but we need to use onlyHere() or remove() at the end
                    Widget::make()
                        ->type('progress')
                        ->class('card mb-4')
                        ->statusBorder('start') // start|top|bottom
                        ->accentColor('info') // primary|secondary|warning|danger|info
                        ->ribbon(['top', 'la-first-order']) // ['top|right|bottom']
                        ->progressClass('progress-bar')
                        ->value($companyCount.' company')
                        ->description('Company count')
                        ->hint('<a href='.backpack_url('company').'>Company List</a>'),
                    // alternatively, you can just push the widget to a "hidden" group
                    Widget::make()
                        ->type('progress')
                        ->class('card mb-4')
                        ->statusBorder('start') // start|top|bottom
                        ->accentColor('danger') // primary|secondary|warning|danger|info
                        ->ribbon(['top', 'la-first-order']) // ['top|right|bottom']
                        ->progressClass('progress-bar')
                        ->value($orderAmountSumAfterCommission)
                        ->description('Wallet Balance')
                        ->hint('Completed order count: '.$orderAmountCompletedCount),
                    Widget::make()
                        ->type('progress')
                        ->class('card mb-4')
                        ->statusBorder('start') // start|top|bottom
                        ->accentColor('primary') // primary|secondary|warning|danger|info
                        ->ribbon(['top', 'la-first-order']) // ['top|right|bottom']
                        ->progressClass('progress-bar')
                        ->value($walletCount)
                        ->description('Wallet Count')
                        ->hint('<a href='.backpack_url('wallet').'>Wallet List</a>'),
                    Widget::make()
                        ->type('progress')
                        ->class('card mb-4')
                        ->statusBorder('start') // start|top|bottom
                        ->accentColor('primary') // primary|secondary|warning|danger|info
                        ->ribbon(['top', 'la-first-order']) // ['top|right|bottom']
                        ->progressClass('progress-bar')
                        ->value($orderAmountRejectSum)
                        ->description('Rejected order')
                        ->hint('Rejected order count: '.$orderAmountRejectCount),
                    Widget::make()
                        ->type('progress')
                        ->class('card mb-4')
                        ->statusBorder('start') // start|top|bottom
                        ->accentColor('primary') // primary|secondary|warning|danger|info
                        ->ribbon(['top', 'la-first-order']) // ['top|right|bottom']
                        ->progressClass('progress-bar')
                        ->value($orderAmountPendingSum)
                        ->description('Pending order')
                        ->hint('Pending order count: '.$orderAmountPendingCount)
            ]);
            } else {
                echo "Not yet";
            }
        }
    }
@endphp

@section('content')
    {{-- In case widgets have been added to a 'content' group, show those widgets. --}}
    @include(backpack_view('inc.widgets'), [ 'widgets' => app('widgets')->where('group', 'content')->toArray() ])
@endsection
