<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomerCategoryRule;
use Illuminate\Http\Request;

class CustomerCategoryRuleController extends Controller
{
    public function index() { return CustomerCategoryRule::orderBy('priority')->get(); }
    public function store(Request $request) { return response()->json(CustomerCategoryRule::create($this->data($request)), 201); }
    public function update(Request $request, CustomerCategoryRule $customerCategoryRule) { $customerCategoryRule->update($this->data($request)); return $customerCategoryRule; }
    public function destroy(CustomerCategoryRule $customerCategoryRule) { $customerCategoryRule->delete(); return ['message' => 'Rule deleted']; }
    private function data(Request $request): array {
        return $request->validate(['name' => 'required', 'category' => 'required|in:Normal,Premium,VIP,HNI', 'minimum_purchase' => 'required|numeric|min:0', 'maximum_purchase' => 'nullable|numeric|gte:minimum_purchase', 'priority' => 'required|integer|min:0', 'is_active' => 'boolean']);
    }
}
