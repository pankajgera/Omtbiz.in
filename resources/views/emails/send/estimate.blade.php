@component('mail::layout')
    {{-- Header --}}
    @slot('header')
        @component('mail::header', ['url' => ''])
        @if($data['company']['logo'])
            <img class="header-logo" src="{{asset($data['company']['logo'])}}" alt="{{$data['company']['name']}}">
        @else
            {{$data['company']['name']}}
        @endif
        @endcomponent
    @endslot

    {{-- Body --}}
    <!-- Body here -->

    {{-- Subcopy --}}
    @slot('subcopy')
        @component('mail::subcopy')
            You have received a new estimate from <span class="company-name">{{$data['company']['name']}}</span>
            @component('mail::button', ['url' => url('/customer/estimates/pdf/'.$data['estimate']['unique_hash'])])
                View Estimate
            @endcomponent
        @endcomponent
    @endslot
     {{-- Footer --}}
     @slot('footer')
        @component('mail::footer')
            {{-- Powered by <a class="footer-link" href="https://omtbizapp.com">omtbiz</a> --}}
        @endcomponent
    @endslot
@endcomponent

