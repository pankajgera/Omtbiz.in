@component('mail::message')
# Introduction
Customer viewed this Estimate.

@component('mail::button', ['url' => url('/estimates/'.$data['estimate']['id'].'/view')])
Estimate
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
