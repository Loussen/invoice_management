{{-- This file is used for menu items by any Backpack v6 theme --}}
<li class="nav-item"><a class="nav-link" href="{{ backpack_url('dashboard') }}"><i class="la la-home nav-icon"></i> {{ trans('backpack::base.dashboard') }}</a></li>

<x-backpack::menu-item title="Companies" icon="la la-landmark" :link="backpack_url('company')" />
<x-backpack::menu-item title="Currencies" icon="la la-euro-sign" :link="backpack_url('currency')" />

<x-backpack::menu-item title="Orders" icon="la la-list" :link="backpack_url('order')" />

<x-backpack::menu-item title="Coins" icon="la la-bitcoin" :link="backpack_url('coin')" />

<x-backpack::menu-item title="Wallets" icon="la la-wallet" :link="backpack_url('wallet')" />
<x-backpack::menu-item title="Versions" icon="la la-code-branch" :link="backpack_url('version')" />
<x-backpack::menu-item title="Users" icon="la la-users" :link="backpack_url('user')" />
