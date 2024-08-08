<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmailSubscriptionRequest;
use App\Models\EmailSubscription;
use Illuminate\Http\Request;

class EmailSubscriptionController extends Controller
{
    protected string $model = EmailSubscription::class;

    protected string $request = EmailSubscriptionRequest::class;

}
