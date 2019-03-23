<?php

namespace App\Model\Sales\SalesDownPayment;

use App\Model\Finance\Payment\Payment;
use App\Model\Form;
use App\Model\Master\Customer;
use App\Model\Sales\SalesContract\SalesContract;
use App\Model\Sales\SalesOrder\SalesOrder;
use App\Model\TransactionModel;
use Illuminate\Http\Request;

class SalesDownPayment extends TransactionModel
{
    protected $connection = 'tenant';

    public $timestamps = false;

    protected $fillable = [
        'customer_id',
        'customer_name',
        'amount',
    ];

    protected $casts = [
        'amount' => 'double',
    ];

    public $defaultNumberPrefix = 'DP';

    public function form()
    {
        return $this->morphOne(Form::class, 'formable');
    }

    /**
     * Get all of the owning downpaymentable models.
     */
    public function downpaymentable()
    {
        return $this->morphTo();
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public static function create($data)
    {
        $downPayment = new self;

        $reference = null;

        if (!empty($data['sales_order_id'])) {
            $downPayment->downpaymentable_id = $data['sales_order_id'];
            $downPayment->downpaymentable_type = SalesOrder::class;

            $reference = SalesOrder::findOrFail($data['sales_order_id']);
        } else if (!empty($data['sales_contract_id'])) {
            $downPayment->downpaymentable_id = $data['sales_contract_id'];
            $downPayment->downpaymentable_type = SalesContract::class;

            $reference = SalesContract::findOrFail($data['sales_contract_id']);
        }

        $downPayment->customer_id = $reference->customer_id;
        $downPayment->customer_name = $reference->customer_name;
        $downPayment->fill($data);
        $downPayment->save();

        $form = new Form;
        $form->fillData($data, $downPayment);

        // Add Payment Collection
        self::addPaymentCollection($data, $downPayment);

        return $downPayment;
    }

    private static function addPaymentCollection($data, $downPayment) {
        $payment = [];
        // payment type should be cash / bank when paid = true
        $payment['payment_type'] = $data['payment_type'] || 'payment collection';
        $payment['due_date'] = $data['due_date'];
        $payment['disbursed'] = true;
        $payment['amount'] = $downPayment->amount;
        $payment['paymentable_id'] = $downPayment->customer_id;
        $payment['paymentable_type'] = Customer::class;
        $payment['paymentable_name'] = $downPayment->customer->name;
        $payment['paid'] = $data['paid'];

        $payment['details'] => [
            0 => [
                'chart_of_account_id' => 1,
                'allocation_id' => null,
                'amount' => $data->,
                'notes' => '',
                'referenceable_type' => get_class($downPayment),
                'referenceable_id' => $downPayment->id,
            ]
        ];

        Payment::create($payment);
    }
}
