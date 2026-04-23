<?php

namespace App\Http\Controllers\Catering;

use App\Http\Controllers\Controller;
use App\Models\Customers\CateringInquiry;
use Illuminate\Contracts\View\View;

class CateringStripeCancelController extends Controller
{
    public function __invoke(CateringInquiry $inquiry): View
    {
        return view('storefront.catering.deposit-cancel', [
            'inquiry' => $inquiry,
        ]);
    }
}
