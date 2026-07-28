<?php

namespace App\Http\Controllers\Api;

use App\Models\Customer;
use App\Services\CustomerCategorizationService;
use App\Services\ActivityService;
use App\Http\Requests\ModuleRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class CustomerController extends CrudController
{
    protected string $model = Customer::class;

    protected array $searchable = ['name', 'mobile', 'email', 'customer_code'];

    protected array $filterable = ['category', 'assigned_to'];

    protected array $with = ['lead'];

    protected function detailWith(): array
    {
        return ['lead', 'familyMembers', 'sales.items', 'customOrders.statusLogs', 'loyaltyPoints', 'giftCards.transactions', 'privilegeCards.issuer'];
    }

    protected function defaults(Request $r): array
    {
        return ['customer_code' => 'CUS-'.now()->format('ymd').'-'.str_pad((string) (Customer::withTrashed()->count() + 1), 4, '0', STR_PAD_LEFT)];
    }

    public function store(ModuleRequest $request, ActivityService $log)
    {
        $response = parent::store($request, $log);
        $customer = Customer::where('mobile', $request->validated('mobile'))->latest('id')->firstOrFail();
        app(CustomerCategorizationService::class)->categorize($customer);
        return $response;
    }

    public function update(ModuleRequest $request, int $id, ActivityService $log)
    {
        $response = parent::update($request, $id, $log);
        app(CustomerCategorizationService::class)->categorize(Customer::findOrFail($id));
        return $response;
    }

    public function import(Request $request, CustomerCategorizationService $categorization)
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,xls|max:10240']);
        $sheet = IOFactory::load($request->file('file')->getRealPath())->getActiveSheet()->toArray(null, true, true, false);
        $headers = array_map(fn ($value) => strtolower(str_replace(' ', '_', trim((string) $value))), array_shift($sheet) ?: []);
        $rows = collect($sheet)->filter(fn ($row) => collect($row)->filter()->isNotEmpty())->take(5000)->map(fn ($row) => array_combine($headers, array_slice(array_pad($row, count($headers), null), 0, count($headers))))->filter(fn ($row) => !empty($row['name']) && !empty($row['mobile']))->values();
        abort_if($rows->isEmpty(), 422, 'Excel must contain Name and Mobile columns.');
        $created = $updated = 0;
        DB::transaction(function () use ($rows, $categorization, &$created, &$updated) {
            foreach ($rows as $row) {
                $customer = Customer::withTrashed()->where('mobile', trim($row['mobile']))->first();
                $values = collect($row)->only(['name', 'mobile', 'email', 'birthday', 'anniversary', 'city', 'address', 'notes'])->filter(fn ($value) => $value !== null && $value !== '')->all();
                if ($customer) {
                    if ($customer->trashed()) $customer->restore();
                    $customer->update($values); $updated++;
                } else {
                    $customer = Customer::create($values + ['customer_code' => 'CUS-'.now()->format('ymd').'-'.str_pad((string) (Customer::withTrashed()->count() + 1), 4, '0', STR_PAD_LEFT)]);
                    $created++;
                }
                $categorization->categorize($customer);
            }
        });
        return ['message' => "{$created} customers created, {$updated} updated", 'created' => $created, 'updated' => $updated];
    }

    public function excelTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray([['Name', 'Mobile', 'Email', 'Birthday', 'Anniversary', 'City', 'Address', 'Notes'], ['Example Customer', '9876543210', 'customer@example.com', '1990-01-15', '2015-02-20', 'Jaipur', 'Customer address', 'Preference notes']]);
        $sheet->getStyle('A1:H1')->getFont()->setBold(true);
        $path = tempnam(sys_get_temp_dir(), 'customers-').'.xlsx';
        (new Xlsx($spreadsheet))->save($path);
        return response()->download($path, 'customer-import-template.xlsx')->deleteFileAfterSend(true);
    }

    public function recategorize(CustomerCategorizationService $categorization)
    {
        Customer::each(fn (Customer $customer) => $categorization->categorize($customer));
        return ['message' => 'Customer categories recalculated'];
    }
}
