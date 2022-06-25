<div class="relative bg-grey-lightest overflow-hidden z-0">
    <img class="absolute hidden pin-b lg:block" style="z-index: -1" src="/images/svg/bg-elements-10.svg" alt="larecipe">
    <img class="absolute pin-b lg:hidden" style="z-index: -1" src="/images/svg/bg-elements-7.svg" alt="larecipe">

    <div class="container pt-12 mt-20 mb-10 px-4 z-20">
        <h1 class="text-grey-darkest text-center font-bold text-3xl mb-4">
            Discover new LaRecipe packages.
        </h1>

        <div class="mt-8 text-center">
            <a href="/docs/2.0/custom-css-and-js" class="button inline-block is-primary text-xl shadow-sm m-2">
                <i class="fa fa-book mr-1 text-grey-lighter"></i> Build A Package
            </a>

            <a href="{{ route('packages.create') }}" class="button inline-block is-white text-xl shadow-sm m-2" style="background: #fff">
                <i class="fa fa-send mr-1 text-grey-dark"></i> Submit A Package
            </a>
        </div>
    </div>
</div>