<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerImportantDate;
use Illuminate\Http\Request;

class CustomerImportantDateController extends Controller
{
    public function index(Customer $customer)
    {
        return $customer->importantDates()->orderBy('date_value')->get();
    }

    public function store(Request $r, Customer $customer)
    {
        $d = $r->validate(['title' => 'required', 'date_type' => 'required|in:birthday,anniversary,spouse_birthday,child_birthday,family_wedding,engagement,baby_ceremony,festival_interest,custom', 'date_value' => 'required|date', 'relation_name' => 'nullable', 'relation_type' => 'nullable', 'notes' => 'nullable', 'is_active' => 'boolean']);

        return response()->json($customer->importantDates()->create($d), 201);
    }

    public function update(Request $r, CustomerImportantDate $customerImportantDate)
    {
        $customerImportantDate->update($r->validate(['title' => 'sometimes|required', 'date_type' => 'sometimes|required', 'date_value' => 'sometimes|date', 'relation_name' => 'nullable', 'relation_type' => 'nullable', 'notes' => 'nullable', 'is_active' => 'boolean']));

        return $customerImportantDate;
    }

    public function destroy(CustomerImportantDate $customerImportantDate)
    {
        $customerImportantDate->delete();

        return ['message' => 'Important date deleted'];
    }
}
