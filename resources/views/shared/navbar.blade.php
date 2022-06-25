<div class="fixed pin-t pin-x z-40">
    <div class="bg-gradient-primary text-white h-1"></div>
    
    <nav class="flex items-center justify-between text-black bg-navbar shadow-xs h-16">
        <div class="flex items-center flex-no-shrink">
            <a href="{{ url('/') }}" class="flex items-center flex-no-shrink text-black ml-4">
                @include("larecipe::partials.logo")

                <p class="inline-block font-semibold ml-1 text-primary">
                    {{ config('app.name') }}
                </p>
            </a>

            <a href="{{ url('/packages') }}" class="flex items-center flex-no-shrink text-black mx-4">
                <p class="inline-block font-semibold ml-1 text-primary hover:text-grey-darker">
                    Packages
                </p>
            </a>
        </div>

        <div class="block mx-4 flex items-center">
            <a href="https://github.com/saleem-hadad/larecipe" target="__blank" class="button is-black mx-2 px-4">
                <i class="fab fa-github"></i>
            </a>

            <a href="/docs" class="button is-primary">
                Docs
            </a>
        </div>
    </nav>
</div>