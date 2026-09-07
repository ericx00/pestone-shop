<?php

namespace App\Http\Controllers;

class PageController extends Controller
{
    /** Static marketing pages built from the company profile. */
    protected array $pages = [
        'about' => 'About Pestone Technologies',
        'business' => 'For Business — B2B accounts, SLAs & procurement',
        'services' => 'ICT & Support Services',
        'legacy-support' => 'Legacy Technology Support',
        'delivery' => 'Delivery & Returns',
        'terms' => 'Terms & Conditions',
        'privacy' => 'Privacy Policy',
    ];

    public function show(string $slug)
    {
        abort_unless(isset($this->pages[$slug]), 404);

        $view = 'pages.'.$slug;
        abort_unless(view()->exists($view), 404);

        return view($view, ['pageTitle' => $this->pages[$slug]]);
    }
}
