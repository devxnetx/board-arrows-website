@extends('layout')

@section('content')
    <article class="sheet">
        <h1>Donate</h1>
        <p>Board Arrows is free. A donation is a thank-you. It does not unlock a feature, and it is not a payment for support.</p>

        <section class="pay">
            <h2>Revolut</h2>
            @if ($revolutUrl)
                <p><a class="cta revolut" href="{{ $revolutUrl }}" rel="noopener noreferrer">Open Revolut</a></p>
            @else
                <p>The Revolut link is not published yet.</p>
            @endif
        </section>

        <section class="pay">
            <h2>Crypto</h2>
            <p>Send only the coin named on that line. A transfer on the wrong network cannot be returned.</p>
            @if (collect($coins)->contains(fn ($coin) => ! empty($coin['example'])))
                <p class="fine">Lines marked Example are samples. Do not send coins to them.</p>
            @endif
            @forelse ($coins as $coin)
                <div class="coin">
                    <strong>{{ $coin['name'] }} <span>{{ $coin['symbol'] }}</span>@if (! empty($coin['example'])) <span class="example">Example</span>@endif</strong>
                    <code id="coin-{{ $coin['symbol'] }}">{{ $coin['address'] }}</code>
                    <button class="choice" type="button" data-copy="{{ $coin['address'] }}">Copy</button>
                </div>
            @empty
                <p>Crypto addresses are not published yet.</p>
            @endforelse
        </section>
    </article>
@endsection

@if ($coins)
    @push('scripts')
        <script>
            document.querySelectorAll("[data-copy]").forEach(function (button) {
                button.addEventListener("click", function () {
                    var address = button.getAttribute("data-copy") || "";
                    navigator.clipboard.writeText(address).then(function () {
                        button.textContent = "Copied";
                    });
                });
            });
        </script>
    @endpush
@endif
