<?php

namespace App\Http\Controllers;

use App\Actions\Companies\SwitchActiveCompany;
use App\Models\Company;
use App\Services\Ui\Toast;
use Illuminate\Http\Request;

class ActiveCompanyController extends Controller
{
    public function store(Request $request, SwitchActiveCompany $switchActiveCompany)
    {
        $validated = $request->validate([
            'company_id' => ['required', 'integer', 'exists:companies,id'],
        ]);

        $company = Company::query()->findOrFail($validated['company_id']);

        $switchActiveCompany->handle($request->user(), $company);

        Toast::flashSuccess('Empresa activa actualizada.');

        return back();
    }
}
