@extends('layout.app')

@section('content')
@include('shared.navbar')
<div class="relative bg-grey-lightest overflow-hidden z-0">
    <img class="absolute hidden pin-b lg:block" style="z-index: -1" src="/images/svg/bg-elements-7.svg" alt="larecipe">

    <div class="container pt-12 my-20 px-4">
        <h1 class="text-grey-darkest text-center font-bold text-3xl mb-10">
            Submit Your Package
        </h1>

        <div class="bg-white rounded-lg p-10 z-50 shadow-md w-full border-t-8 border-primary">
            <h3 class="mb-8 text-grey-dark text-center leading-normal">
                Submit your package information and we'll start processing the application as soon as possible.
            </h3>

            @if ($errors->any())
                <div class="bg-red-lightest py-4 rounded">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li class="text-red py-1">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('packages.store') }}">
                @csrf

                <div class="my-4">
                    <p class="text-lg mb-2">Author Name</p> 
                    <input type="text" name="author_name" value="{{ old('author_name') }}" placeholder="Saleem Hadad" required="required" class="bg-white rounded border border-grey-lighter focus:outline-none focus:border-grey p-4 w-full">
                </div>
                <div class="my-4">
                    <p class="text-lg mb-2">Email Address</p> 
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="saleem@test.com" required="required" class="bg-white rounded border border-grey-lighter focus:outline-none focus:border-grey p-4 w-full">
                </div>
                <div class="my-4">
                    <p class="text-lg mb-2">Package Name</p> 
                    <input type="text" name="package_name" value="{{ old('package_name') }}" placeholder="LaRecipe Dark Theme" required="required" class="bg-white rounded border border-grey-lighter focus:outline-none focus:border-grey p-4 w-full">
                </div>
                <div class="my-4">
                    <p class="text-lg mb-2">Package Type</p>
                    <div class="inline-block relative w-full">
                        <select name="package_type" class="block appearance-none w-full bg-white border border-grey-light hover:border-grey p-4 pr-8 rounded border border-grey-lighter leading-tight focus:outline-none focus:shadow-outline">
                            <option value="Theme">Theme</option>
                            <option value="Tool">Tool</option>
                        </select>
                        <div class="pointer-events-none absolute pin-y pin-r flex items-center px-2 text-grey-darker">
                            <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                        </div>
                    </div>
                </div>
                <div class="my-4">
                    <p class="text-lg mb-2">Packagist Namespace</p> 
                    <input type="text" name="packagist_namespace" value="{{ old('packagist_namespace') }}" placeholder="binarytorch" required="required" class="bg-white rounded border border-grey-lighter focus:outline-none focus:border-grey p-4 w-full">
                </div>
                <div class="my-4">
                    <p class="text-lg mb-2">Packagist Project Name</p> 
                    <input type="text" name="packagist_project_name" value="{{ old('packagist_project_name') }}" placeholder="larecipe-dark-theme" required="required" class="bg-white rounded border border-grey-lighter focus:outline-none focus:border-grey p-4 w-full">
                </div>
                <div class="my-4">
                    <p class="text-lg mb-2">Url</p> 
                    <input type="text" name="url" value="{{ old('url') }}" placeholder="e.g. GitHub" required="required" class="bg-white rounded border border-grey-lighter focus:outline-none focus:border-grey p-4 w-full">
                </div>
                <div class="my-4">
                    <p class="text-lg mb-2">Short Description</p> 
                    <input type="text" name="description" value="{{ old('description') }}" placeholder="A short description about your package" required="required" class="bg-white rounded border border-grey-lighter focus:outline-none focus:border-grey p-4 w-full">
                </div>
                <div class="text-right"><button type="submit" class="bg-primary text-white rounded shadow-md py-4 px-8">Submit</button></div>
            </form>
        </div>
    </div>
</div>
@include('shared.footer')
@endsection