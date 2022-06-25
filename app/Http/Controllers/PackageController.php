<?php

namespace App\Http\Controllers;

use App\Models\Package;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    /**
     * @return mixed
     */
    public function index()
    {
        $themes = Package::where('approved', true)
                            ->where('package_type', 'Theme')
                            ->get();

        $tools = Package::where('approved', true)
                            ->where('package_type', 'Tool')
                            ->get();

        return view('packages.index', [
            'themes' => $themes,
            'tools'  => $tools
        ]);
    }

    /**
     * @return mixed
     */
    public function show($vendor, $project)
    {
        $package = Package::where('packagist_namespace', $vendor)
                            ->where('packagist_project_name', $project)
                            ->firstOrFail();

        abort_if(! $package->approved, 403);

        return view('packages.show', [
            'package' => $package
        ]);
    }

    /**
     * @return mixed
     */
    public function create()
    {
        return view('packages.create');
    }

    /**
     * @return mixed
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'author_name'            => 'required|string|max:254',
            'email'                  => 'required|email|max:254',
            'package_name'           => 'required|string|max:254',
            'package_type'           => 'required|string|max:254|in:Theme,Tool',
            'packagist_namespace'    => 'required|string|max:254',
            'packagist_project_name' => 'required|string|max:254|unique:packages',
            'url'                    => 'required|url|max:254',
            'description'            => 'required|string|max:254',
        ]);

        Package::create($data);

        return redirect()->back()->with('success', 'Application submitted, Thank you!');
    }
}
