<?php

namespace App\Http\Controllers;

use App\Models\Vacancy;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class HrVacancyController extends Controller
{
    public function index()
    {
        return view('hr.index', ['vacancies' => Vacancy::latest()->paginate(20)]);
    }

    public function create()
    {
        return view('hr.form', ['vacancy' => new Vacancy]);
    }

    public function store(Request $request)
    {
        $vacancy = Vacancy::create($this->validated($request));

        return to_route('hr.vacancies.edit', $vacancy)->with('status', 'Lowongan berhasil disimpan.');
    }

    public function edit(Vacancy $vacancy)
    {
        return view('hr.form', compact('vacancy'));
    }

    public function update(Request $request, Vacancy $vacancy)
    {
        $vacancy->update($this->validated($request));

        return to_route('hr.vacancies.edit', $vacancy)->with('status', 'Perubahan lowongan berhasil disimpan.');
    }

    private function validated(Request $request): array
    {
        $rules = [
            'location' => ['required', 'string', 'max:200'],
            'employment_type' => ['required', Rule::in(['full_time', 'contract', 'internship'])],
            'application_email' => ['required', 'email', 'max:254'],
            'status' => ['required', Rule::in(['draft', 'published', 'closed'])],
            'closing_date' => ['nullable', 'date_format:Y-m-d'],
            'translations' => ['required', 'array:id,en,zh'],
        ];
        foreach (['id', 'en', 'zh'] as $locale) {
            $rules['translations.'.$locale] = [$locale === 'id' ? 'required' : 'nullable', 'array:title,description,requirements'];
            foreach (['title' => 200, 'description' => 20000, 'requirements' => 20000] as $field => $max) {
                $required = $locale === 'id' || collect($request->input('translations.'.$locale, []))->contains(fn ($value) => filled($value));
                $rules['translations.'.$locale.'.'.$field] = [$required ? 'required' : 'nullable', 'string', 'max:'.$max];
            }
        }

        return $request->validate($rules);
    }
}
