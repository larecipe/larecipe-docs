<section class="bg-grey-lightest relative">
    <div class="container pt-10">
        <div class="mb-10">
            <p class="text-grey-darkest font-bold text-2xl sm:text-2xl md:text-3xl lg:text-3xl mb-2">
                Themes
            </p>
            <div class="bg-pink w-12 h-1 rounded-full"></div>
        </div>

        <div class="md:flex md:flex-wrap md:-ml-4">

            @foreach($themes as $theme)
                <a href="{{ $theme->path() }}" class="md:w-1/3 mb-10 md:mb-20 px-4">
                    <div class="bg-white h-full shadow-md hover:shadow rounded-lg overflow-hidden" style="transition: box-shadow .1s">
                        <div class="h-48 w-full bg-grey" style="background: url({{ $theme->photo }}); background-size: cover; background-position: center;"></div>

                        <div class="p-4">
                            <h3 class="text-grey-darker mb-2">{{ $theme->package_name }}</h3>
                            <p class="text-grey-dark font-thin">{{ $theme->description }}</p>
                        </div>
                    </div>
                </a>
            @endforeach

            <a href="/docs/2.0/custom-css-and-js#new-theme" class="md:w-1/3 mb-10 md:mb-20 px-4">
                <div class="bg-blue-darker h-full shadow-md hover:shadow rounded-lg overflow-hidden" style="transition: box-shadow .1s">
                    <div class="h-48 w-full bg-blue-darkest flex justify-center items-center">
                        <img class="h-32 w-32" src="/images/icons/package.svg" alt="package icon">
                    </div>

                    <div class="p-4">
                        <h3 class="text-grey-lighter mb-2">Create Your Theme</h3>
                        <p class="text-grey-light font-thin">Learn how to create a LaRecipe theme</p>
                    </div>
                </div>
            </a>
        </div>
    </div>


    <div class="container pb-10">
        <div class="my-10">
            <p class="text-grey-darkest font-bold text-2xl sm:text-2xl md:text-3xl lg:text-3xl mb-2">
                Tools &amp; Assets
            </p>
            <div class="bg-pink w-12 h-1 rounded-full"></div>
        </div>

        <div class="md:flex md:flex-wrap md:-ml-4">
            @foreach($tools as $tool)
                <a href="{{ $tool->path() }}" class="md:w-1/3 mb-10 md:mb-20 px-4">
                    <div class="bg-white h-full shadow-md hover:shadow rounded-lg overflow-hidden" style="transition: box-shadow .1s">
                        <div class="h-48 w-full bg-grey" style="background: url({{ $tool->photo }}); background-size: cover; background-position: center;"></div>

                        <div class="p-4">
                            <h3 class="text-grey-darker mb-2">{{ $tool->package_name }}</h3>
                            <p class="text-grey-dark font-thin">{{ $tool->description }}</p>
                        </div>
                    </div>
                </a>
            @endforeach

            <a href="/docs/2.0/custom-css-and-js#new-tool-or-asset"  class="md:w-1/3 mb-10 md:mb-20 px-4">
                <div class="bg-blue-darker h-full shadow-md hover:shadow rounded-lg overflow-hidden" style="transition: box-shadow .1s">
                    <div class="h-48 w-full bg-blue-darkest flex justify-center items-center">
                        <img class="h-32 w-32" src="/images/icons/package.svg" alt="package icon">
                    </div>

                    <div class="p-4">
                        <h3 class="text-grey-lighter mb-2">Create Your Assets</h3>
                        <p class="text-grey-light font-thin">Learn how to create custom components</p>
                    </div>
                </div>
            </a>
        </div>
    </div>
</section>