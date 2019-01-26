<?php

namespace App\Model\Purchase\PurchaseRequest;

use App\Model\Form;
use App\Model\HumanResource\Employee\Employee;
use App\Model\Master\Supplier;
use App\Model\Purchase\PurchaseOrder\PurchaseOrder;
use App\Model\TransactionModel;

class PurchaseRequest extends TransactionModel
{
    protected $connection = 'tenant';

    public $timestamps = false;

    protected $fillable = [
        'required_date',
        'employee_id',
        'employee_name',
        'supplier_id',
        'supplier_name',
    ];

    protected $defaultNumberPrefix = 'PR';

    public function form()
    {
        return $this->morphOne(Form::class, 'formable');
    }

    public function items()
    {
        return $this->hasMany(PurchaseRequestItem::class);
    }

    public function services()
    {
        return $this->hasMany(PurchaseRequestService::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class)
            ->joinForm(PurchaseOrder::class)
            ->active();
    }

    public static function create($data)
    {
        $purchaseRequest = new self;
        // TODO validation employee_name is optional type non empty string
        if (empty($data['employee_name'])) {
            $employee = Employee::find($data['employee_id'], ['name']);
            $data['supplier_name'] = $employee->name;
        }
        // TODO validation supplier_name is optional type non empty string
        if (empty($data['supplier_name'])) {
            $supplier = Supplier::find($data['supplier_id'], ['name']);
            $data['supplier_name'] = $supplier->name;
        }
        $purchaseRequest->fill($data);
        $purchaseRequest->save();

        $form = new Form;
        $form->fillData($data, $purchaseRequest);

        // TODO validation items is optional and must be array
        $items = $data['items'] ?? [];
        if (!empty($items) && is_array($items)) {
            $array = [];
            foreach ($items as $item) {
                $purchaseRequestItem = new PurchaseRequestItem;
                $purchaseRequestItem->fill($item);
                $purchaseRequestItem->purchase_request_id = $purchaseRequest->id;
                array_push($array, $purchaseRequestItem);
            }
            $purchaseRequest->items()->saveMany($array);
        }

        // TODO validation services is required if items is null and must be array
        $services = $data['services'] ?? [];
        if (!empty($services) && is_array($services)) {
            $array = [];
            foreach ($services as $service) {
                $purchaseRequestService = new PurchaseRequestService;
                $purchaseRequestService->fill($service);
                $purchaseRequestService->purchase_request_id = $purchaseRequest->id;
                array_push($array, $purchaseRequestService);
            }
            $purchaseRequest->services()->saveMany($array);
        }

        return $purchaseRequest;
    }
}
