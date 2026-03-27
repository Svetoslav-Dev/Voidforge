@extends('layouts.app', ['title' => 'Cookie Policy | VoidForgeStore'])

@section('content')
    <section class="card hero legal-page">
        <div>
            <h1>Cookie Policy</h1>
            <p class="lead">This page should describe which cookies or similar technologies are used on the storefront and which of them are essential for the site to function.</p>
        </div>
    </section>

    <section class="card legal-card">
        <h2>Essential cookies</h2>
        <p>Laravel session and security-related cookies are required so login, cart, checkout, queued form flows, and CSRF protection can work correctly.</p>

        <h2>Optional tracking</h2>
        <p>Optional analytics or marketing cookies are not enabled by default in the current storefront. If they are added later, they should be listed here together with their purpose and consent rules.</p>

        <h2>Customer choice</h2>
        <p>The site now includes a cookie-preferences banner and a footer shortcut so visitors can keep essential cookies only or allow optional cookies later.</p>

        <h2>Current operational scope</h2>
        <p>The current storefront uses cookies and browser storage primarily for session continuity, cart handling, and saving the visitor's cookie choice. No optional marketing or analytics cookies are enabled by default.</p>
    </section>
@endsection
