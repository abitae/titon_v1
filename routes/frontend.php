<?php

use Illuminate\Support\Facades\Route;

Route::livewire('/', 'frontend::index')->name('home');
Route::livewire('/nosotros', 'frontend::nosotros')->name('frontend.about');
Route::livewire('/proyectos', 'frontend::proyectos')->name('frontend.projects');
Route::livewire('/contacto', 'frontend::contacto')->name('frontend.contact');
