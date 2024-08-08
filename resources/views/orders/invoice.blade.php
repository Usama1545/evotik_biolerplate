<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- Google fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@100;200;300;400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100;0,9..40,200;0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;0,9..40,800;0,9..40,900;0,9..40,1000;1,9..40,100;1,9..40,200;1,9..40,300;1,9..40,400;1,9..40,500;1,9..40,600;1,9..40,700;1,9..40,800;1,9..40,900;1,9..40,1000&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="{{ \URL::to('/css/invoice.css') }}?ver={{ time() }}">

  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body>
  @foreach ($order->order_items->chunk(5) as $order_items)
  <div class="flex min-h-screen flex-col">
    <div class="flex min-h-screen flex-col py-10 mx-auto" style="width: 100%; max-width: calc(100vw - 200px) !important">
      <img class="h-[35px] mx-auto mb-3" src="{{ brand_settings()['logo_light'] }}" />
      <div class="mb-4 grid grid-cols-2 items-start justify-between">
        <div style="width: max-content;">
          <span class="text-2xl font-semibold">{{ brand_settings()['store_name'] }}</span>
          <br />
          <div class="flex items-start justify-between" style="width: max-content;">
            <section dir="ltr" class="rtl:text-right text-left">
              <span>{{ brand_settings()['address'] }}</span>
              <br />
              <span>{{ brand_settings()['phone'] }}</span>
              <br />
              <span>{{ brand_settings()['email'] }}</span>
            </section>
          </div>
        </div>
        <div class="relative rtl:text-left text-right">
          <h2 class="mb-3 text-2xl font-semibold text-gray-800">@lang('invoice.Invoice')</h2>
          <div class="absolute rtl:left-0 ltr:right-0 flex flex-col items-end space-y-5">
            <div>
              {!! $order?->invoice?->barcode !!}
              <small class="mt-3 font-medium">{{ $order->invoice->uid }}</small>
            </div>
            <div>
              {!! $order?->invoice?->qrcode !!}
              <small class="mt-3 font-medium">{{ $order->invoice->uid }}</small>
            </div>
          </div>
        </div>
      </div>

      <div class="mb-5">
        <span class="font-bold">@lang('invoice.Bill to:')</span>
        <br />
        <span>{{ $order?->customer?->name }}</span><br />
        <span dir="ltr">{{ $order?->customer->cc . $order?->customer->phone_number }}</span>
        <br />
        <span>{{ $order?->customer->email }}</span>
        <br />
        <span>{{ $order?->address?->address }},
          <br>
          {{ $order?->address?->city?->name }},
          <br>
          {{ $order?->address?->country?->name }}
        </span>
      </div>
      <div class="my-4 flex items-center justify-between">
        <span> @lang('invoice.Order number'): {{ $order->uid }} </span>
        <span> @lang('invoice.Order Date'): {{ $order->created_at->translatedFormat('d M Y') }} </span>
      </div>

      <div class="mb-3 overflow-hidden rounded-md border border-gray-300 shadow">
        <table class="w-full rtl:text-right text-left">
          <thead class="border-b border-gray-300">
            <tr>
              <th class="px-2 py-2">@lang('invoice.Product')</th>
              <th class="w-[90px] px-2 py-2">@lang('invoice.Quantity')</th>
              <th class="w-[150px] px-2 py-2">@lang('invoice.Price')</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($order_items as $item)
            <tr class="border-b">
              <td class="px-3 py-2">
                <div class="flex items-center space-x-3 rtl:space-x-reverse">
                  <div class="h-[55px] w-[55px] flex-shrink-0 overflow-hidden rounded-md border bg-gray-100 dark:border-gray-700 dark:bg-gray-800">
                    <img src="{{ $item->model->image }}" class="h-full w-full object-contain" />
                  </div>
                  <div>
                    <span class="font-medium">{{ $item->model->product->name }}</span>
                    <br />
                    <span class="text-gray-700">{{ $item->model->name }}</span>
                    <br />
                    <span class="text-gray-700" dir="ltr">({{ $item->price / $item->quantity }} {{ currency('iso_3') }} x
                      {{ $item->quantity }})</span>
                  </div>
                </div>
              </td>
              <td class="px-3 py-2">{{ $item->quantity }}</td>
              <td class="px-3 py-2" dir="ltr">{{ $item->price }} {{ currency('iso_3') }}</td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      <table class="w-full rtl:text-right text-left">
        <tbody>
          <tr>
            <td></td>
            <td class="w-[240px]">
              <div class="flex items-center justify-between"><span class="font-semibold">@lang('invoice.Sub total'):</span>
                <span>{{ $order->sub_total }} {{ currency('iso_3') }}</span>
              </div>
              <div class="flex items-center justify-between"><span class="font-semibold">@lang('invoice.Shipping'):
                </span><span>{{ $order->shipping_fee }} {{ currency('iso_3') }}</span></div>
              <div class="flex items-center justify-between"><span class="font-semibold">@lang('invoice.Tax'):
                </span><span>{{ $order->tax }} {{ currency('iso_3') }}</span></div>
              <div class="flex items-center justify-between"><span class="font-semibold">@lang('invoice.Total'):
                </span><span>{{ $order->total_price }} {{ currency('iso_3') }}</span></div>
            </td>
          </tr>
        </tbody>
      </table>
      <div class="mt-auto prose prose-p:my-1" style="font-size: 12px; width: 100%; max-width: 100% !important">
        {!! brand_settings()['invoice_notes'] !!}
      </div>
      <div class="mt-3 text-sm">
        <p class="text-sm"><strong>@lang('invoice.Phone'): </strong><a href="tel:{{ visitorCountry()->setting->phone_number }}" dir="ltr">{{ visitorCountry()->setting->phone_number }}</a></p>
        <p class="text-sm"><strong>@lang('invoice.Whatsapp'): </strong><a href="tel:{{ visitorCountry()->setting->whatsapp_number }}" dir="ltr">{{ visitorCountry()->setting->whatsapp_number }}</a></p>
      </div>
      <small class="mt-3" style="font-weight: 600; text-align: center;">
        {{ brand_settings()['store_name'] }} &copy; {{ now()->format('Y') }}
      </small>
    </div>
  </div>
  @endforeach
</body>

</html>