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
            echo "";
        } else {
            $companyIds = $admin->companies()->pluck('companies.id');

            if($companyIds->isNotEmpty()) {
                $orderCount = \App\Models\Order::whereIn('company_id', $companyIds)->count();

                $orderAmountSum = \App\Models\Order::whereIn('company_id', $companyIds)->sum('amount');

                $companyCount = $companyIds->count();

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
                        ->description('Orders count'),
                    // alternatively, to use widgets as content, we can use the same add() method,
                    // but we need to use onlyHere() or remove() at the end
                    Widget::make()
                        ->type('progress')
                        ->class('card mb-4')
                        ->statusBorder('start') // start|top|bottom
                        ->accentColor('success') // primary|secondary|warning|danger|info
                        ->ribbon(['top', 'la-first-order']) // ['top|right|bottom']
                        ->progressClass('progress-bar')
                        ->value($companyCount.' company')
                        ->description('Company count'),
                    // alternatively, you can just push the widget to a "hidden" group
                    Widget::make()
                        ->type('progress')
                        ->class('card mb-4')
                        ->statusBorder('start') // start|top|bottom
                        ->accentColor('success') // primary|secondary|warning|danger|info
                        ->ribbon(['top', 'la-first-order']) // ['top|right|bottom']
                        ->progressClass('progress-bar')
                        ->value($orderAmountSum)
                        ->description('Balance'),
            ]);
            } else {
                echo "sadas";
            }
        }
    }
@endphp

@section('content')
    {{-- In case widgets have been added to a 'content' group, show those widgets. --}}
    @include(backpack_view('inc.widgets'), [ 'widgets' => app('widgets')->where('group', 'content')->toArray() ])
@endsection
