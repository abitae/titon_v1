<?php

use App\Models\SiteSetting;
use App\Services\Frontend\SiteContentService;
use Database\Seeders\SiteContentSeeder;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    $this->seed(SiteContentSeeder::class);
});

test('section returns site setting models from cache', function () {
    $content = app(SiteContentService::class);

    $first = $content->section('home.hero');
    $second = $content->section('home.hero');

    expect($first)->toBeInstanceOf(SiteSetting::class)
        ->and($second)->toBeInstanceOf(SiteSetting::class)
        ->and($second->id)->toBe($first->id)
        ->and($second->title)->toBe($first->title);

    expect(Cache::get('site-content.section.home.hero'))->toBeArray();
});

test('section recovers from invalid cached data', function () {
    Cache::forever('site-content.section.home.hero', new stdClass);

    $setting = app(SiteContentService::class)->section('home.hero');

    expect($setting)->toBeInstanceOf(SiteSetting::class)
        ->and($setting->key)->toBe('home.hero');
});

test('brand helpers return seeded brand data', function () {
    $content = app(SiteContentService::class);

    expect($content->brand())->toBeInstanceOf(SiteSetting::class)
        ->and($content->brandName())->toBe(config('app.name', 'Titon'));
});

test('sections by prefix returns site setting models from cache', function () {
    $content = app(SiteContentService::class);

    $first = $content->sectionsByPrefix('home');
    $second = $content->sectionsByPrefix('home');

    expect($first)->not->toBeEmpty()
        ->and($first->first())->toBeInstanceOf(SiteSetting::class)
        ->and($second->first()->id)->toBe($first->first()->id);

    expect(Cache::get('site-content.prefix.home'))->toBeArray();
});
