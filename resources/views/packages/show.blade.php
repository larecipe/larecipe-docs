@extends('layout.app')

@section('content')
@include('shared.navbar')
<div class="relative bg-grey-lightest overflow-hidden z-0">
    <img class="absolute hidden pin-b lg:block" style="z-index: -1" src="/images/svg/bg-elements-7.svg" alt="larecipe">

    <div class="container pt-12 my-20 px-4">
        <a href="{{ route('packages.index') }}" class="text-indigo font-bold text-sm block mb-6 no-underline">
            <i class="fa fa-arrow-left"></i>   Back
        </a>

        <h1 class="text-grey-darkest font-bold text-3xl mb-4">
            {{ $package->package_name }}
        </h1>

        <div class="w-full lg:flex lg:-ml-2">
            <div class="lg:w-3/4 mx-2 my-4">
                <div class="bg-white rounded shadow-sm overflow-hidden w-full border-t-4 border-primary">
                    <h3 class="text-grey-darker font-normal p-4 pb-2 mb-0">
                        {{ $package->description }}
                    </h3>
                    
                    <hr class="h-1 border-t border-grey-light">

                    <div class="readme documentation overflow-scroll is-dark expanded w-full" style="padding: 1rem; margin-top: 0; margin-bottom: 0;">
                        @if($package->readme_link)
                            {!! (new ParsedownExtra)->text(file_get_contents($package->readme_link)) !!}
                        @endif
                    </div>
                </div>
            </div>

            <div class="lg:w-1/4 mx-2 my-4">
                <div class="bg-white rounded shadow-xs w-full overflow-hidden border-t-4 border-pink">
                    <div class="hover:bg-grey-lightest p-4 border-b">
                        <h5 class="text-grey-dark mb-1">Added</h5>
                        <p class="text-black">{{ $package->created_at->diffForHumans() }}</p>
                    </div>

                    <div class="hover:bg-grey-lightest p-4 border-b">
                        <h5 class="text-grey-dark mb-1">Author Name</h5>
                        <p class="text-black">{{ $package->author_name }}</p>
                    </div>

                    <div class="hover:bg-grey-lightest p-4 border-b">
                        <h5 class="text-grey-dark mb-1">Type</h5>
                        <p class="text-black">{{ $package->package_type }}</p>
                    </div>

                    <div class="hover:bg-grey-lightest p-4">
                        <h5 class="text-grey-dark mb-1">URL</h5>
                        <a href="{{ $package->url }}" target="__blank" class="text-blue">{{ $package->url }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@include('shared.footer')
@endsection