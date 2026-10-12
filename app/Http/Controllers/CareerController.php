<?php

namespace App\Http\Controllers;

use App\Models\Vacancy;
use Illuminate\Contracts\View\View;

class CareerController extends Controller
{
    public function index(string $locale): View
    {
        app()->setLocale($locale);

        return view('welcome', ['locale' => $locale, 'copy' => trans('company'), 'article' => null,
            'careerPage' => true, 'vacancies' => Vacancy::published()->latest()->paginate(10), 'vacancy' => null]);
    }

    public function show(string $locale, string $vacancy): View
    {
        app()->setLocale($locale);

        return view('welcome', ['locale' => $locale, 'copy' => trans('company'), 'article' => null,
            'careerPage' => true, 'vacancy' => Vacancy::published()->findOrFail($vacancy)]);
    }
}
